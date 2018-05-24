# zf-state-machine

Этот модуль позволяет организовать в вашем приложении 
стейт-машины ([недетерминированные конечные автоматы - НКА](https://ru.wikipedia.org/wiki/%D0%9A%D0%BE%D0%BD%D0%B5%D1%87%D0%BD%D1%8B%D0%B9_%D0%B0%D0%B2%D1%82%D0%BE%D0%BC%D0%B0%D1%82#%D0%94%D0%B5%D1%82%D0%B5%D1%80%D0%BC%D0%B8%D0%BD%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D0%BD%D0%BD%D0%BE%D1%81%D1%82%D1%8C)
), которые позволят выполнять дополнительные действия при переходе объектов 
из одного состояния в другое или сразу после перехода.

[Top](README.md) | [English documentation](README_en.md)

Особенностями данного НКА является:
-----------------------------------
1. использование [Doctrine2](http://doctrine2.readthedocs.io/en/stable/tutorials/getting-started.html) для описания списка состояний, действий и переходов
1. Использование стандартных валидаторов ZF для проверки возможности действий
1. Использование [функциональных объектов](https://ru.wikipedia.org/wiki/%D0%A4%D1%83%D0%BD%D0%BA%D1%86%D0%B8%D0%BE%D0%BD%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9_%D0%BE%D0%B1%D1%8A%D0%B5%D0%BA%D1%82) 
(функторов) для выполнения дополнительных действий при выполнении действия над объектом
 или после него.
1. защита от зацикливания при каскадном вызове НКА

## Оглавление
1. [Область применения](#Application_area)
1. [Принцип работы](#How_it_works)
1. Пример реализации и использования:
    1. [Создаем классы таблиц](#Create_table_classes)
    1. [Создаем класс стейтмашины](#Create_class_of_state_machine)
    1. [Описываем конфигурацию](#description_of_the_configuration)
    1. [Создаем валидаторы и функторы](#Create_validators)
    1. [Используем](#Use_it)
1. Внутренняя организация
    1. [Основные методы](#Basic_public_methods)
    1. [Матрица переходов](#Transition_matrix)
    1. [Валидаторы действий](#Validators)
    1. [Функторы](#Functors)
    1. [Транзакции, flush() и etc](#Transactions_flush)
    1. [Каскадные вызовы и защита от зацикливания](#Recursive_calls)

<a name="Application_area"></a>
## Область применения
В приложениях часто необходимо ограничить доступ к тем или иным действиям
над объектом. Управление правами на действие успешно реализуется с
помощью [RBAC](https://ru.wikipedia.org/wiki/%D0%A3%D0%BF%D1%80%D0%B0%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B5_%D0%B4%D0%BE%D1%81%D1%82%D1%83%D0%BF%D0%BE%D0%BC_%D0%BD%D0%B0_%D0%BE%D1%81%D0%BD%D0%BE%D0%B2%D0%B5_%D1%80%D0%BE%D0%BB%D0%B5%D0%B9)
-модулей. Однако RBAC-модуль контролирует право на действие 
пользователя в зависимости от роли, но не контролирует возможность совершения
действия в зависимости от состояния объекта. 
Пример: ведение пропусков. Вася может редактировать пропуск, но до тех пор, пока
пропуск не выдан.
Данная зачача успешно решается при помощи конечного автомата ([finite-state machine](https://ru.wikipedia.org/wiki/%D0%9A%D0%BE%D0%BD%D0%B5%D1%87%D0%BD%D1%8B%D0%B9_%D0%B0%D0%B2%D1%82%D0%BE%D0%BC%D0%B0%D1%82#%D0%94%D0%B5%D1%82%D0%B5%D1%80%D0%BC%D0%B8%D0%BD%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D0%BD%D0%BD%D0%BE%D1%81%D1%82%D1%8C)).

<a name="How_it_works"></a>
## Принцип работы:

Объект - это доктриновская "сущность"(entity), которая имеет 
свойство, хранящее состояние объекта (обычно это связь много к одному к словарю
состояний).

Словарь действий - это доктриновская "сущность" - словарь возможных действий
над нашим объектом.

Матрица переходов - это две сущности A и B, связанные между собой отношением
один ко многим.
   
Для объекта, который имеет словарь состояний, описывается словарь действий 
и матрица переходов из состояния в состояние при выполнении действия. Т.к. у нас
недетерминированный конечный автомат (НКА), то выполнение действия может приводить объект
из исходного состояние в одно из нескольких других состояний согласно
матрице переходов, в том числе оставлять в исходном.

В метод НКА doAction подается объект, действие, которое хотим совершить над
объектом и дополнительные данные.  
1. НКА проверяет возможность совершения действия:
    1. наличие действия для объекта
    1. возможность выполнения действия над объектом в текущем состоянии согласно матрицы
     переходов
    1. дополнительные проверки,например, права на действие для данного пользователя
1. при успешной проверке
    1. определяется новое состояние, в которое будет переведен объект
    1. выполняются, если определены, действия перед переходом в новое состояние
    1. объект переводится в новое состояние
    1. выполняются, если определены, действия после перехода объекта в новое состояние.

<a name="Example_of_implementation"></a>
## Пример реализации и использования

Рассмотрим использование НКА на примере пропуска на транспортное стредство.
Пропуск имеет два состояния:
1. черновик (draft)
1. выдан (active)

В состоянии черновик его можно:
 1. смотреть (view)
 1. редактировать (edit)
 1. выдать (issue)

В состоянии active можно только смотреть (view). 

<a name="Create_table_classes"></a>
#### Создаем классы таблиц
1. [Словарь состояний пропуска](example/Entity/PassTicketCar.php)
1. [Пропуск](example/Entity/)
1. [Словарь действий (или переходов)](example/Entity/PassTicketAction.php)
1. [Таблица переходов A](example/Entity/TransitionATicketCar.php)
1. [Таблица переходов B](example/Entity/TransitionBTicketCar.php)

Загружаем данные в словари состояний, действий, и таблицы переходов:
1. [pass_ticket_status.sql](example/Sql/pass_ticket_status.sql)
1. [pass_ticket_action.sql](example/Sql/pass_ticket_action.sql)
1. [tr_a_ticket_car.sql](example/Sql/tr_a_ticket_car.sql)
1. [tr_b_ticket_car.sql](example/Sql/tr_b_ticket_car.sql)

В таблице pass_ticket_car создаем запись - пропуск, в поле pass_ticket_status_id записываем 'draft'

Tаблицы переходов представлены таблицами tr_a_ticket_car(таблица A) и tr_b_ticket_car(таблица B).
Какие поля для чего предназначены можно прочитать в разделе [Внутренняя организация--Матрица переходов](#transition)

<a name="Create_class_of_state_machine"></a>
#### Создаем класс стейтмашины
1. Наследуемся от базового абстрактного класса \KotaShade\StateMachine\Service\StateMachine
2. Реализуем абстрактные методы
    1. abstract protected function getTransitionARepository();
    2. abstract protected function getActionEntity($action);
    
    Пример реализации:
    ```php
    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected function getTransitionARepository()
    {
        $ret = $this->em->getRepository(TransitionATicketCar::class);
        return $ret;
    }

    protected function getActionEntity($action)
    {
        $repo = $this->em->getRepository(PassTicketAction::class);
        $ret = $repo->find($action);
        return $ret;
    }

    ```
    
    По умолчанию предполагается, что объект хранит свое состояние в свойстве `state` и существуют геттер и сеттер для него.
    
    В нашем случае свойство называется `passTicketStatus`, поэтому перегружаем методы 
    `getObjectState($objE)` и `setObjectState($objE, $stateE)`
    [пример реализации](example/Ticketcar.php)
    
    
<a name="description_of_the_configuration"></a>
#### Описываем конфигурацию.
конфигурацию валидаторов и функторов включим в module.config.php. Предлагаю конфигурацию валидаторов
и функторов держать в отдельных файлах и включать их в module.config.php. 
Например:
```php
$validator = include(__DIR__ . '/validators.config.php');
$smConfig = include(__DIR__ . '/state_machine.config.php');

return array_merge(
    $validator,
    $smConfig,
    [
    .....
```
В конфигурации будем использовать алиасы на валидаторы (это даст нам возможность использовать эти алиасы в таблицах перехода A и B). 
Валидаторы будут создаваться стандартным образом через ValidatorManager
```php
use Test\Validator as ValidatorNS;
use Test\StateMachine\TicketCar\Validator as SM_TCValidatorNS;

return [
    'validators' => [
        'aliases' => [
            //=============== TicketCar===========================
            'SM_TC_Draft_view' => SM_TCValidatorNS\ViewChain::class,
            'SM_TC_Draft_edit' => SM_TCValidatorNS\EditChain::class,
            'SM_TC_Draft_issue' => SM_TCValidatorNS\EditChain::class,

            'SM_TC_Active_view' => SM_TCValidatorNS\ViewChain::class,
        ],
```
Валидаторы будут найдены и созданы обычным путем с помощью ValidatorManager.

Функторы:
```php
use Test\StateMachine\Functor as RootFunctorNS;
use Test\StateMachine\TicketCar\Functor as TCFunctorNS;

return [
    KotaShade\StateMachine\Functor\FunctorProviderInterface::CONFIG_KEY => [
        'aliases' => [
            //================== PassTicketCar ================
            'SMF_TC_Draft_edit' => TCFunctorNS\Edit::class,
            'SMF_TC_Draft_issue' => TCFunctorNS\Issue::class,
        ],
        'abstract_factories' => [
        ],
        'factories' => [
            RootFunctorNS\EmptyFunctor::class => RootFunctorNS\EmptyFunctorFactory::class,
            TCFunctorNS\Edit::class => RootFunctorNS\BaseFunctorFactory::class,
            TCFunctorNS\Issue::class => RootFunctorNS\BaseFunctorFactory::class,
        ],
        'invokables' => [

        ],
    ]
];
```
Функторы будут найдены и созданы с помощью FunctorPluginManager, по аналогии с сервисами.

<a name="Create_validators"></a>
#### Создаем валидаторы и функторы
Пример реализации можно посмотреть здесь:
1. [EditChain](example/Validator/EditChain.php)
1. [BaseChain](example/Validator/BaseChain.php)

Использование валидаторов, пронаследованных от ValidatorChain позволит легко наращивать и изменять характер проверок.
В частности один из валидаторов в цепи ValidatorChain
может проверять права на данное действие через RBAC

В функторе выполняем дополнительные действия, которые необходимо выполнить при выполнении данного действия.
[Edit.php](example/Functor/Edit.php) - пример реализации.

<a name="Use_it"></a>
#### Используем
Ниже код - пример использования в действии ActionController-а для проверки доступности дейсвия, а также
 для выполнения самого действия.  

```php
    $sm = $this->getSeviceLocator();
    $em = $this->getEntityManager();
    /** @var \Test\Entity\PassTicketCar $objE */
    $objE =$em->find(\Test\Entity\PassTicketCar::class, $id);
    if ($objE == null) {
        throw new \Exception('Не найден пропуск с идентификатором id=', $id);
    }

    /** @var \Test\StateMachine\TicketCar $stateMachine */
    $stateMachine = $sm->get(\Test\StateMachine\TicketCar::class);
    if ($stateMachine->hasAction($objE, $action) == false)  {
        throw new \Exception(sprintf('Объект %d не имеет в текущем состоянии %s действия %s',
            $objE->getId(), $objE->getPassTicketStatus()->getId(), $action));
    }

............
    $em->beginTransaction();
    try {
        $stateMachine->doAction($objE, $action, ['sm' => $stateMachine]);
        $em->flush();
        $em->commit();
        echo 'URA action done';
    } catch(\Throwable $e) {
        $em->rollback();
        echo 'FAIL action =' . $action . $e->getMessage();
    }

```

## Внутренняя организация
<a name="Transition_matrix"></a>
#### Матрица переходов
<a name="transition"></a>
Матрица переходов описывается связкой двух таблиц A и B.

В таблице A имеем:
1. `src_id` - идентификатор исходного состояния объекта (внешний ключ к словарю состояний пропуска),
1. `action_id`- идентификатор дейсвия над объектом (внешний ключ к словарю действий),
1. `condition` - имя/алиас валидатора, который будет проверять возможность совершения действия
Использование валидаторов-наследников `Zend\Validator\ValidatorChain`
позволяет описать множество проверок, объединенных логическим AND и легко расширяемо при необходимости
добавить еще одну проверку.

В таблице B имеем связанные с записью из таблицы А одну или несколько записей содержащих:
1. `transition_a_id` - идентификатор связи с записью из таблицы А,
1. `dst_id` - идентификатор нового состояния объекта (внешний ключ к словарю состояний пропуска),
1. `weight` - вес перехода (объяснение ниже),
1. `condition` - условие выбора данного перехода - алиас валидатора или null,
1. `pre_functor` - имя/алиас функтора, который будет выполнен перед сменой состояния объекта,
1. `post_functor` - имя/алиас функтора, который будет выполнен после смены состояния объекта.

Если действие может привести только к одному новому состоянию, тогда `weight` не важен, а `condition` оставляем null.
Если же нужно, чтобы одно действие могло приводить к одному из списка состояний, тогда в таблице В будет несколько
записей связанных с одной из таблицы А, при этом задаются `weight`, и `condition` (алиас валидатора проверки постусловия). 
Записи будут проверяться в порядке уменьшения веса. Первая же запись, у которой проверка постусловия будет успешной,
будет определять конечное состояние и выполняемые функторы.
Если поле `condition` is null - считается, что проверка успешна. Размещайте ее с наименьшим весом.

НКА выполнен в виде абстрактного класса. 
Для реализации конкретного НКА необходимо определить 2 абстрактных метода
1. abstract protected function getTransitionARepository(); - получение репозитория А-таблицы
2. abstract protected function getActionEntity($action); - получение ентити действия по строковому имени.
 
Если состояние объекта хранится не в свойстве state, тогда необходимо переопределить методы
`getObjectState($objE)` и `setObjectState($objE, $stateE)`

Для удобства создания объекта стейтмашины можно воспользоваться абстрактой фабрикой `KotaShade\StateMachine\Service\StateMachineAbstractFactory`

<a name="Basic_public_methods"></a>
#### Основные методы
```php
/**
 * Выполняется действие над объектом и меняет состояние объекта согласно таблицы переходов
 * @param object $objE
 * @param string $action
 * @param array $data  extra data
 * @return array
 * @throws ExceptionNS\StateMachineException
 */
public function doAction($objE, $action, array $data = [])

/**
 * Проверяет возможность выполнения действия над объектом в текущем состоянии
 * @param object $objE
 * @param string $action
 * @param array $data
 * @return bool
 */
public function hasAction($objE, $action, $data=[])

/**
* возвращает список действий, которые существуют для данного состояния без учета проверок на возможность выполнения
* @param $state
* @return array
* @throws \Doctrine\Common\Persistence\Mapping\MappingException
* @throws \ReflectionException
*/
public function getActionsForState($state)

/**
* возвращает список возможных действий над объектом в текущем состоянии с учетом проверок
* @param object $objE
* @param array $data
* @return array
*/
public function getActions($objE, $data=[])

```

<a name="Validators"></a>
#### Валидаторы действия

Объекты, реализующие `\Zend\Validator\ValidatorInterface`, подчиняющиеся всем стандартным правилам создания и использования вадидаторов в ZF.
В метод isValid() будет передан объект, над которым совершается действие и массив доп.данных `$data`, передаваемый в методы `doAction()`, `hasAction()`

<a name="Functors"></a>
#### Функторы
Единственное требование к функтору - реализация интерфейса `\KotaShade\StateMachine\Functor\FunctorInterface`

[Edit.php](example/Functor/Edit.php) - пример реализации. 

Функтор в свою очерень может вызывать другие стейтмашины.

Функторы условно можно разделить на префункторы и постфункторы. Их реализация ничем не 
отличается, просто первые вызываются до смены состояния объекта, а вторые уже после.
Желающие использовать событийную модель могут легко реализовать функторы, которые будут 
бросать нужные им события.

<a name="Transactions_flush"></a>
#### Транзакции, flush() и etc

НКА не управляет транзакциями, не вызывает flush(), commit(), rollback(). 

<a name="Recursive_calls"></a>
#### Каскадные вызовы и защита от зацикливания

Внутри функторов вы можете вызывать другие стейтмашины или эту же стейтмашину, но с другим объектом. 
Можно даже вызвать стейтмашину с тем же объектом,
но состояние его уже должно измениться, то есть это возможно в постфункторе. Иногда (редко)
это необходимо для организации каскадно выполняемых действий.
Иначе при определнении зацикливания будет выброшено исключение LoopException

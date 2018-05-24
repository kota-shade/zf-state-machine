# zf-state-machine
Non-deterministic Finite State Machine for Zend Framework.

Russian documentation is [here](README_ru.md).

This module allows you to organize state machine 
([non-deterministic finite state machines - NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton))
 in your application. That will allow you to restrict the list of actions in current object state 
and to perform additional operations during object's transition
from one state to another or immediately after the transition.


The features of this non-deterministic finite state machine are:
-----------------------------------
1. Use [Doctrine2] (http://doctrine2.readthedocs.io/en/stable/tutorials/getting-started.html) to describe a list of States, actions, and transitions
1. Using standard Zend Framework validators to verify what actions are possible for the object.
1. The use of [function objects](https://en.wikipedia.org/wiki/Function_object) (functors) to perform additional actions
 during transition or after it.
1. Protection from infinite loops in a recursive NFA calls

## Content
1. Scope
1. How it works
1. Example of implementation and use:
    1. Create table classes
    1. Create the class of state machine
    1. The description of the configuration
    1. Create validators and functors
    1. Use it!
1. Internal organization
    1. Transition matrix
    1. Basic public methods
    1. Validators for the action
    1. Functors
    1. Transactions, flush () and etc
    1. Recursive calls and infinite loops defence
    

## Scope
An application is often needs to restrict access to certain actions on the object.
[RBAC](https://en.wikipedia.org/wiki/Role-based_access_control)
-modules successfully do these types of restrictions.
However, the RBAC module controls the action grants by roles, but does not control the possibility of actions doing
 depending on the state of the object. 
For example: the issuing of the pass. Bob can edit the pass, but as long as
the pass is not issued.
This task successfully solves by using a finite state machine ([NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton)).

## How it works:

The `object` is the Doctrine entity with a property that stores the state of the object (usually a many-to-one relationship to the states dictionary.)

The `actions` dictionary is the Doctrine entity - dictionary of possible actions.

The `transition matrix`: the two entities `A` and `B` that are related by a relationship
one to many.
   
For an object that has a state (from state dictionary), we describe the actions dictionary 
and the `transition matrix`. 
`Transition matrix` describes transitions from one to another state
when performing an action. NFA allows us to have the same state, one other state or one of several states
after doing the action.

The NFA method get the object, the action name and the additional data. 
1. NFA checks the possibility of the action:
    1. there is the action for the object
    1. ability to do the action on the object in the current state according to the `transition matrix`
    1. additional checks, such as action grants for the current user
1. if checks was successful:
    1. find the new state for the object
    1. if some operations are defined to perform before state changing, do these operations
    1. change the object state to a new state
    1. if some operations are defined to perform after state changing, do these operations

## Example of implementation and using
Look the car pass ticket system.
The car pass ticket has 2 states:
 1. draft
 1. active

We can do in the `draft` state:
 1. view
 1. edit
 1. issue

We can do in the `active` state:
 1. view
  
#### Create table classes
1. [Dictionary of states](example/Entity/PassTicketCar.php)
1. [Car ticket pass](example/Entity/)
1. [Dictionary of actions](example/Entity/PassTicketAction.php)
1. [Transition table A](example/Entity/TransitionATicketCar.php)
1. [Transition table B](example/Entity/TransitionBTicketCar.php)

Load data into database tables:
1. [pass_ticket_status.sql](example/Sql/pass_ticket_status.sql)
1. [pass_ticket_action.sql](example/Sql/pass_ticket_action.sql)
1. [tr_a_ticket_car.sql](example/Sql/tr_a_ticket_car.sql)
1. [tr_b_ticket_car.sql](example/Sql/tr_b_ticket_car.sql)

Create the pass ticket row in the pass_ticket_car table. Set `draft` into `pass_ticket_status_id` field.
There are transition tables: `tr_a_ticket_car` (table A) и `tr_b_ticket_car` (table B).
Detail fields description you can read in [Internal organization--Transition matrix](#transition)

#### Create the class of state machine
1. Create your state machine class, extend it from abstract class \KotaShade\StateMachine\Service\StateMachine.
2. Overload abstract methods:
    1. abstract protected function getTransitionARepository();
    2. abstract protected function getActionEntity($action);
    
    Example:
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
    
    By default, it is assumed that the object stores its state in the `state` property and there is a getter and a setter for it.
    
    In our case, the property is called 'passTicketStatus', so we overload the methods. 
    `getObjectState($objE)` and `setObjectState($objE, $stateE)`
    [example](example/Ticketcar.php)
    
    

#### The description of the configuration.

The configuration of validators and functors will be included in the module.config.php. I suggest to keep configuration of validators
and the functors in separate files and include them in the `module.config.php`.
Example:
```php
$validator = include(__DIR__ . '/validators.config.php');
$smConfig = include(__DIR__ . '/state_machine.config.php');

return array_merge(
    $validator,
    $smConfig,
    [
    .....
```
Use aliases for validators (we can use them in transition tables tr_a_ticket_car and tr_b_ticket_car).
The ValidatorManager is used for validator creation. 

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

Functor configuration example from `state_machine.config.php`:
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
The `FunctorPluginManager` helps to create `functors` like services.

#### Create validators and functors
Example:
1. [EditChain](example/Validator/EditChain.php)
1. [BaseChain](example/Validator/BaseChain.php)

Hint:
-----
Using validators, which extended by `ValidatorChain` will allow you to easily add and modify checks.
In particular one of the validators in a `ValidatorChain` chain
can check the rights to this action via `RBAC`

The functor contains additional code which you want to do during the changing object state.
This is the functor example. 
[Edit.php](example/Functor/Edit.php).

#### Use it!
The following code is an example of how to use an controller's action to check the availability of the action, and
 to perform the action itself.

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
## Internal organization
#### Transition matrix
<a name="transition"></a>
The transition matrix is described by two tables A and B.

The `table A` has fields:
1. `src_id` - the ID of the original state of the object (foreign key to the dictionary of object state),
1. `action_id`- action ID of the object (foreign key to the action dictionary),
1. `condition` - name / alias of the validator which will check the possibility of the action

Hint:
The use of validators extended from `Zend\Validator\ValidatorChain`
allows you to describe a variety of checks, combined by logical AND.

Table B is linked by a one-to-many relationship to `table A` and contains:
1. `transition_a_id` - ID of the link to the record from `table A`,
1. `dst_id` - the ID of the new state of the object (foreign key to the dictionary of object states),
1. `weight` - transition weight (explanation below),
1. `condition` - the condition of this transition - the validator name/alias or null,
1. `pre_functor` - name / alias of the functor to be executed before changing the state of the object,
1. `post_functor` - name / alias of the functor to be executed after changing the state of the object,

If the action can only lead to one new state, then `weight` is not important and `condition` is null.
If you want one action to be able to result in one of the list of states, then `table B` will have several
records associated with one of the `table A`, these records are set to `weight`, and `condition` (name/alias of the postcondition check validator).
Records will be checked in order of weight reduction. The first entry where the postcondition check is successful,
will determine the final state and the functors to be executed.
If the `condition` field is null, the check is considered successful. Place it with the least weight.

The base class of NFA is abstract.
Expand it and define 2 abstract methods.
1. abstract protected function getTransitionARepository(); - get A-table repository
2. abstract protected function getActionEntity($action); - get entity of action by name.

If the state of the object is not stored in the state property, then you must override the methods
`getObjectState($objE)` and `setObjectState($obj, $stateE)` 

You can use the abstract factory `KotaShade\StateMachine\Service\StateMachineAbstractFactory` for state machine object easy creation.

#### Basic public methods
```php
/**
 * do action on object change object state according transition table
 * @param object $objE
 * @param string $action
 * @param array $data  extra data
 * @return array
 * @throws ExceptionNS\StateMachineException
 */
public function doAction($objE, $action, array $data = [])

/**
 * Checks whether the action on the object can be performed in the current state
 * @param object $objE
 * @param string $action
 * @param array $data
 * @return bool
 */
public function hasAction($objE, $action, $data=[])

/**
* return action list on the object in the current state without validator check
* @param $state
* @return array
* @throws \Doctrine\Common\Persistence\Mapping\MappingException
* @throws \ReflectionException
*/
public function getActionsForState($state)

/**
* return action list on the object in the current state WITH validator check
* @param object $objE
* @param array $data
* @return array
*/
public function getActions($objE, $data=[])

```

#### Validators for the action

Objects that implement `\Zend\Validator\ValidatorInterface`, obeying all the standard rules of creation and use of validation in ZF.
The method isValid() is called with object parameter and external data array 
( the same external data which is passed to `doAction()`, `hasAction()` methods ).

#### Functors

The only requirement for the functor is interface implementation `\KotaShade\StateMachine\Functor\Functor Interface`.

This is example: [Edit.php](example/Functor/Edit.php). 

The functor, in turn, can call other NFA. The functor can be called before changing of the object state or after this. 

Функторы условно можно разделить на префункторы и постфункторы. Их реализация ничем не 
отличается, просто первые вызываются до смены состояния объекта, а вторые уже после.
Желающие использовать событийную модель могут легко реализовать функторы, которые будут 
бросать нужные им события.

#### Transactions, flush () and etc

НКА не управляет транзакциями, не вызывает flush(), commit(), rollback(). 

#### Recursive calls and infinite loops defence

Внутри функторов вы можете вызывать другие стейтмашины или эту же стейтмашину, но с другим объектом. 
Можно даже вызвать стейтмашину с тем же объектом,
но состояние его уже должно измениться, то есть это возможно в постфункторе. Иногда (редко)
это необходимо для организации каскадно выполняемых действий.
Иначе при определнении зацикливания будет выброшено исключение LoopException


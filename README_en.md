# zf-state-machine
Non-deterministic Finite State Machine for Zend Framework.

[Top](README.md) | [Russian documentation](README_ru.md)

The features of this non-deterministic finite state machine are:
-----------------------------------
1. [Doctrine2](http://doctrine2.readthedocs.io/en/stable/tutorials/getting-started.html) is used to describe a list of states, actions and transitions
1. Standard Zend Framework validators is used to verify what actions are possible for the object.
1. [Function objects](https://en.wikipedia.org/wiki/Function_object) (functors) is used to perform additional actions
 during transition or after it.
1. There is protection from infinite loops in a recursive NFA calls

## Content
1. [Application area](#Application_area)
1. [How it works](#How_it_works)
1. Example of the implementation and usage:
    1. [Create table classes](#Create_table_classes)
    1. [Create the class of state machine](#Create_class_of_state_machine)
    1. [The description of the configuration](#description_of_the_configuration)
    1. [Create validators and functors](#Create_validators)
    1. [Use it!](#Use_it)
1. Internal organization
    1. [Transition matrix](#Transition_matrix)
    1. [Basic public methods](#Basic_public_methods)
    1. [Validators for the action](#Validators)
    1. [Functors](#Functors)
    1. [Transactions, flush () and etc](#Transactions_flush)
    1. [Recursive calls and loopback protection](#Recursive_calls)
    

<a name="Application_area"></a>
## Application area

Often the application must restrict the access to certain actions on the object.
[RBAC](https://en.wikipedia.org/wiki/Role-based_access_control)
-modules do these types of restrictions successfully.
The RBAC module controls the actions by roles and permissions. But RBAC does not control the possibility of actions depending on the state of the object.
For example: the pass ticket system. Bob can edit,view,issue the pass while it is in the draft state, but when the pass is issued Bob can only view it.
This task is successfully solved by using a finite state machine ([NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton)).

<a name="How_it_works"></a>
## How it works:

The `object` is the Doctrine entity. It has a property that stores the state of the object (usually a many-to-one relationship to the states dictionary.)

The `actions` is the Doctrine entity. It is the dictionary of possible actions.

The `transition matrix` are two entities `A` and `B` that are related by the
one-to-many relationship.
   
The object has the state from state dictionary, and we describe the action's dictionary and the `transition matrix`. 
`Transition matrix` describes transitions from one to another object's state.
When we do the `action` on the object the NFA changes the object state from one to another according to `transition matrix`. 
Because we have a nondeterministic finite state machine 
([NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton)), 
the execution of an action can move an object from the initial state to one of several other states according 
to the transition matrix, including leaving it in the original state.


The [NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton) method `doAction()`  gets the object, 
the action name and the additional data.

1. The NFA verifies whether the action is possible:
    1. the action itself 
    1. the possibility to perform an action with an object in the current state according to the `transition matrix`
    1. additional checks, for example, if the right to perform act is granted for this user
1. on successful verification:
    1. a new state in which the object will be transferred is defined according to the `transition matrix`
    1. if defined, actions before transition to a new state are performed
    1. the object is transferred to a new state
    1. if additional actions are defined, after the object has been transferred to a new state, they are performed
    
<a name="Example_of_implementation"></a>
## Example of implementation and usage
Look the car pass ticket system.
The car pass ticket could have 2 states:
 1. draft
 1. active

What we can do in the `draft` state:
 1. view
 1. edit
 1. issue

What we can do in the `active` state:
 1. view

<a name="Create_table_classes"></a>
#### The creation of table classes
Let's create classes of tables

1. [Dictionary of states](example/Entity/PassTicketCar.php)
1. [Car ticket pass](example/Entity/)
1. [Dictionary of actions](example/Entity/PassTicketAction.php)
1. [Transition table A](example/Entity/TransitionATicketCar.php)
1. [Transition table B](example/Entity/TransitionBTicketCar.php)

Let's load data into database tables:
1. [pass_ticket_status.sql](example/Sql/pass_ticket_status.sql)
1. [pass_ticket_action.sql](example/Sql/pass_ticket_action.sql)
1. [tr_a_ticket_car.sql](example/Sql/tr_a_ticket_car.sql)
1. [tr_b_ticket_car.sql](example/Sql/tr_b_ticket_car.sql)

Let's create the row in the table `pass_ticket_car` and
set `draft` into `pass_ticket_status_id` field.
There are transition tables: `tr_a_ticket_car` (table A) и `tr_b_ticket_car` (table B).
Names of fields and their description are in the section [Internal organization--Transition matrix](#transition)

<a name="Create_class_of_state_machine"></a>
#### Create the class of state machine

1. Create your state machine class, extend it from 
abstract class \KotaShade\StateMachine\Service\StateMachine.
2. Implement abstract methods:
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
    
    In our case, the property is called 'passTicketStatus', so we reload the methods. 
    `getObjectState($objE)` and `setObjectState($objE, $stateE)`
    
    [example](example/TicketCar.php)
    
    
<a name="description_of_the_configuration"></a>
#### The description of the configuration.

The configuration of validators and functors will be included in the module.config.php. 
I would like to keep configuration of validators
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
The ValidatorManager is used for validators creation. 

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

<a name="Create_validators"></a>
#### Create validators and functors

Example:
1. [EditChain](example/Validator/EditChain.php)
1. [BaseChain](example/Validator/BaseChain.php)

Hint:
Using validators, inherited from `ValidatorChain` will allow you to easily add and modify checks.
In particular one of the validators in a `ValidatorChain` chain
can check the rights to this action via `RBAC`

The functor contains additional code you must do during the changing object state.
This is the functor example. 
[Edit.php](example/Functor/Edit.php).

<a name="Use_it"></a>
#### Use it!
The following code is an example of how to use the controller's action to check 
the availability of the action and to perform the action itself.

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
<a name="Transition_matrix"></a>
#### Transition matrix
<a name="transition"></a>
The transition matrix is described by two tables A and B.

The `table A` includes the following fields:
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

If the action can only lead to one new state, then `weight` is not important, 
and we leave `condition` as null. 
If you want one action to lead to one of the list of states, 
then there will be several records in `Table B` associated with one record of the table A.
This records in `table B` must have different weight and condition (the postcondition validator verification alias). 
Records will be checked in order of weight descending. The first record, in which the postcondition check 
is successful, will determine the final state and executed functors. 
If the `condition` field is null, it is considered that the check is successful. Place it with the smallest `weight`.

The base class of NFA is abstract.
Expand it and define 2 abstract methods.
1. abstract protected function getTransitionARepository(); - get A-table repository
2. abstract protected function getActionEntity($action); - get entity of action by name.

If the state of the object is not stored in the state property, then you must redefine methods
`getObjectState($objE)` and `setObjectState($obj, $stateE)` 

You can use the abstract factory `KotaShade\StateMachine\Service\StateMachineAbstractFactory` for state machine object easy creation.

<a name="Basic_public_methods"></a>
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

<a name="Validators"></a>
#### Validators for the action

Validators are the classes that implement `\Zend\Validator\ValidatorInterface`.
The validator method `isValid()` is called with two parameters: the object and external data array 
 which is passed to `doAction()`, `hasAction()` methods.

<a name="Functors"></a>
#### Functors

The only requirement for the functor is interface implementation `\KotaShade\StateMachine\Functor\Functor Interface`.

This is example: [Edit.php](example/Functor/Edit.php). 

The functor, in turn, can call other NFA. The functor can be called before changing of the object state or after this. 

<a name="Transactions_flush"></a>
#### Transactions, flush () etc

NFA doesn't manage transaction, doesn't call [Doctrine2](http://doctrine2.readthedocs.io/en/stable/tutorials/getting-started.html) flush(), commit(), rollback(). 
The programmer should use these on his own.

<a name="Recursive_calls"></a>
#### Recursive calls and loopback protection

Inside the functors you can call other [NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton)
 or the same NFA but with a different object. 
You can even call a statemachine with the same object,
but the state must already have to change, that is possible in post-functor. 
Otherwise, the loop is found and the LoopException exception will be thrown.


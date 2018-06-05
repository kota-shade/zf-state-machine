# zf-state-machine
Non-deterministic Finite State Machine for Zend Framework.

This module allows you to organize state machine 
([non-deterministic finite state machines - NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton))
 in your application. That will allow you to restrict the list of actions in current object state 
and to perform additional operations during object's transition
from one state to another or immediately after the transition.

## Application area
Often the application must restrict the access to certain actions on the object.
[RBAC](https://en.wikipedia.org/wiki/Role-based_access_control)
-modules do these types of restrictions successfully.
The RBAC module controls the actions by roles and permissions. But RBAC does not control the possibility of actions depending by the state of the object.
For example: the pass ticket system. Bob can edit,view,issue the pass while it is in the draft state, but when the pass is issued Bob can only view it.
This task is successfully solved by using a finite state machine ([NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton)).

The features of this non-deterministic finite state machine are:
-----------------------------------
1. [Doctrine2] (http://doctrine2.readthedocs.io/en/stable/tutorials/getting-started.html) is used to describe a list of states, actions and transitions
1. Standard Zend Framework validators is used to verify what actions are possible for the object.
1. [Function objects](https://en.wikipedia.org/wiki/Function_object) (functors) is used to perform additional actions
 during transition or after it.
1. There is protection from infinite loops in a recursive NFA calls

[English documentation](README_en.md) | [Russian documentation](README_ru.md).


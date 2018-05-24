# zf-state-machine
Non-deterministic Finite State Machine for Zend Framework.

This module allows you to organize state machine 
([non-deterministic finite state machines - NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton))
 in your application. That will allow you to restrict the list of actions in current object state 
and to perform additional operations during object's transition
from one state to another or immediately after the transition.

## Application area
An application is often needs to restrict access to certain actions on the object.
[RBAC](https://en.wikipedia.org/wiki/Role-based_access_control)
-modules successfully do these types of restrictions.
However, the RBAC module controls the action grants by roles, but does not control the possibility of actions doing
 depending on the state of the object. 
For example: the issuing of the pass. Bob can edit the pass, but as long as
the pass is not issued.
This task successfully solves by using a finite state machine ([NFA](https://en.wikipedia.org/wiki/Nondeterministic_finite_automaton)).

The features of this non-deterministic finite state machine are:
-----------------------------------
1. Use [Doctrine2] (http://doctrine2.readthedocs.io/en/stable/tutorials/getting-started.html) to describe a list of States, actions, and transitions
1. Using standard Zend Framework validators to verify what actions are possible for the object.
1. The use of [function objects](https://en.wikipedia.org/wiki/Function_object) (functors) to perform additional actions
 during transition or after it.
1. Protection from infinite loops in a recursive NFA calls

[English documentation](README_en.md) | [Russian documentation](README_ru.md).


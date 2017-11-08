# Welcome to Weave

Imagine being 7 years old again, with some friends, playing after school. You've got a huge box full of dressing up stuff and limitless imagination. You want to play super-heros? There's stuff for that. How about film stars? Or the Wild West? Or fantasy worlds with Orcs and Elves? It's all in the dressing up box. All the tops, trousers, dresses, shoes, hats, foam swords, plastic laser guns and sparkly amulets you'd ever want to bring your creations to life.

In the world of PHP, Packagist is packed to the hilt with the code equiv. of foam swords and hats - there are limitless combinations. But if you are trying to assemble a well known group of packages, such as for a micro-framework middleware stack, there's still a lot of work and boilerplate to stick all the bits together. So, to save others the effort, there are some party-costume packs of ready-grouped clothes and accessories you can just pick off the rack. Everything you need to achieve that space-pirate look.

But what if you want to be a space-pirate with a foam sword and a big hat NOT the space-pirate someone else decided you would want, with the plastic laser gun and the clear plastic space helmet? Unfortunately, in code, it's often not as easy as swapping swords with your best friend.

When it comes to PHP frameworks, it would be great to have the freedom to pick the bits you want from the dressing up box but without the effort of then having to tie it all together by hand.

Welcome to Weave.

## Anatomy of a Micro-Framework

Whichever micro-framework you look at, they all have the same basic components (give or take):

* Configuration
* Error Handler
* Dependency Injection Controller
* Middleware Stack
* Router
* Resolve/Dispatch

In each case, you are expected to write your secret awesome extras as Middleware components, Controllers etc. and hang them from the framework in the spaces provided and in the order you want. In each case, you have little or no say as to which of the above components you get to use. Some micro-frameworks do attempt some choice, such as Zend Expressive, but it is limited.

But look on Packagist and you will find loads of different DICs, Middleware Stacks, Routers etc. and there's some great inovation out there.

This is where Weave is different. Weave doesn't come bundled with most of the components listed. Instead, you get to pick Adaptors - or write your own. Whatever suits you best.

## Pick Your Own

Weave has Adaptors for the different Components it needs. You provide one of each Adaptor (or your own code) to create the microframework you want.

Component | Adaptors | Custom options
----------|----------|----------------
Configuration | Zend Config | Provide a single method in your App class that returns an array.
Error Handling | Whoops | Provide a single method in your App class.
DIC | League, Aura.Di | Provide a single method in your App Class that returns a callable.
Middleware Stack | Relay, Middleman 2.x | Implementation of the MiddlewareAdaptorInterface.
Router | FastRoute, Aura.Router | Implementation of the RouterAdaptorInterface.
Resolver | Internal | Implementation of the ResolveAdaptorInterface.
PSR7 | Zend Diactoros | Implementation of the PSR7 standard.

Where Interfaces are in use, you simply map them to concrete classes in your DIC.

Take a look at some of the examples at http://github.com/weavephp to see how this works in practice.
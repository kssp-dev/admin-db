:::{php:namespace} Atk4\Core
:::

# Debug Trait

:::{php:trait} DebugTrait
:::

## Introduction

Agile Core implements ability for application to implement "debug", "info" and
"messages". The general idea of them is that they can be generated in the depths
of the code, but the application will receive and process this information based
on the defined settings.

Sample scenario would be if some of the components tries to perform operation
which fails and it is willing to pass information about this failure to the app.
This is not as extreme as exception, but still, user needs to be able to find
this information eventually.

### Compatibility with PSR-3

Loggers as implemented by PSR-3 define message routing with various LogLevels,
but it's intended for logging only. The Debug Trait covers a wider context as
described below:

### Debug

The design goal of Debug is to be able to display contextual debug information
only when it's manually enabled. For instance, if you are having problem with
user authentication, you should enable `$auth->debug(true)`. On other hand - if
you wish to see persistence-related debug info, then `$db->debug(true)` will
enable that.

Information logged through debug like this on any object that implements
DebugTrait:

```
$this->debug('Things are bad');
$this->debug('User {user} created', ['user' => $user]);
```

The Application itself can use DebugTrait too and normally should do, making it
possible to use `$this->getApp()->debug()`.

Various objects may implement DebugTrait and also invoke $this->debug(), but in
most cases this will simply be ignored right away unless you manually enable
debugging for the object:

```
$obj1->debug(true); // enable debugging
$obj1->debug(false); // disable debugging
$obj1->debug(true); // also enables debugging

$obj1->debug('test1'); // will go to logger
$obj2->debug('test2'); // will not go to logger because debug is not enabled for this object
```

Executing debug will look for `$this->getApp()` link and if the application
implements `Psr\Log\LoggerInterface`, then `$this->getApp()->log()` will be
called using LogLevel DEBUG.

### Log

Log method will log message every time. DebugTrait implements the `log()`
method which will either display information on the STDOUT (if `$this->getApp()`
does not exist or does not implement PSR-3)

### debugTraceChange

This method can help you find situations when a certain code is called multiple
times and when it shouldn't. When called first time it will remember "trace"
which is used to arrive at this point. Second time it will compare with the
previous and will tell you where trace has diverged.

This method is pretty valuable when you try to find why certain areas of the
code have executed multiple times.

## Properties

## Methods

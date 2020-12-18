# PhPuml

## About PhPuml

PhPuml is a console tool that creates PlantUML class diagram definitions (`*.puml`) from PHP code, written in PHP.

Here's a class diagram of the tool, created by itself:

![PhPuml class diagram](./doc/img/ph-puml.svg)
[<img src="./doc/img/ph-puml.svg" width="816">](./doc/img/ph-puml.svg)

And the [generated puml file](./doc/src/ph-puml.puml) the diagram is based on.

## Features

 * Convenient installation via composer
 * Packages from Namespaces
 * Generates inheritance relationships for classes, interfaces and traits
 * Generates class properties & method signatures, including type hints from @var doc comments
 * Dependencies inferred from constructor argument types (assumes dependency injection)
 * Associations inferred from new expressions (creates), throw statements (throws)   
 * Works on Linux (tested), Windows (tested), macOS (probably)

## Requirements

 * PHP 7.4 only for now
 * Composer 2

## Installation

The preferred way to install PhPuml globally through composer.

```console
$ composer global require jhofm/ph-puml
```

If you already have composer's global vendor/bin folder in your PATH, the tool can be executed by calling the script ```ph-puml```.
Otherwise the executable can be found at ``~/.composer/vendor/bin/ph-puml``.

Alternatively you can also clone this repository and install the composer dependencies yourself.

```console
$ git clone git@github.com:jhofm/ph-puml.git
$ cd ph-puml
$ composer install
$ ./bin/ph-puml
```  

## Quick Start

The `ph-puml` script will output PlantUML code describing all PHP files found in the current 
folder. Alternatively it accepts a relative or absolute path to a target directory or file as an argument.

```bash
$ ph-puml src/tests
```

The resulting PlantUML code is written to the console (and can of course be piped into a file instead).

```bash
$ ph-puml > class.puml
```

If the target path is a directory, PhPuml will recursively find files with the file extension ```.php``` there.
Folders called `vendor` in target directory are ignored by default. 
If you want to include a vendor folder, disable the exclusion regex like this:

```console
$ ph-puml --exclude ""
```

You can also define your own exclusions (only regular expressions are supported currently):
   
```console
$ ph-puml --exclude "~^foo~" --exclude "~bar$~"
```   
   
PhPuml uses `symfony/command`, so you can always check out the command's help page including all supported arguments and options.   

```console
$ ph-puml -h
```

## Limitations

* PhPuml is able to handle huge amounts of code files, but for your sanity's sake i'd advise using it on smaller packages.
* Cleaner code will yield better results. Typehints and Namespaces help a whole lot, for example.
* There's a lot of polishing still to be done, like inferring additional relation types or providing more customisations.
* When parsing directories, only files with the file extension ``*.php`` are currently included. 
* Auto generated class diagrams will probably never exactly meet your needs, but at least they should provide a starting point and save you some mind-numbing work.  

## Troubleshooting

* `Uncaught Error: Class Composer\InstalledVersions not found`: PhPuml requires Composer 2
* `require(): Failed opening required ...`: Run composer install 

## Acknowledgements

This would have been exponentially more difficult to do without [Nikita Popov](https://github.com/nikic)'s [PHP-Parser](https://github.com/nikic/PHP-Parser),
so many thanks for that. And [symfony](https://github.com/symfony) helped a lot, as usual.   

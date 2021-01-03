# PhPuml

[![Packagist](https://img.shields.io/packagist/l/jhofm/ph-puml.svg?style=flat-square)](https://packagist.org/packages/jhofm/ph-puml)
[![Packagist](https://img.shields.io/packagist/v/jhofm/ph-puml.svg?style=flat-square)](https://packagist.org/packages/jhofm/ph-puml)
[![Packagist](https://img.shields.io/packagist/php-v/jhofm/ph-puml.svg?style=flat-square)](https://packagist.org/packages/jhofm/ph-puml)
[![CI Workflow](https://img.shields.io/github/workflow/status/jhofm/ph-puml/CI.svg?style=flat-square)](https://github.com/jhofm/ph-puml/actions)


## About PhPuml

PhPuml generates PlantUML class diagrams from PHP code.

Here's a class diagram of the tool, created by itself:

![PhPuml class diagram](./doc/img/ph-puml.svg)

## Features

 * Convenient installation via composer
 * Generate PlantUml files without having PlantUML installed or generate all supported formats (png, svg, latex, etc) using a `plantuml.jar` executable
 * Packages from Namespaces
 * Generates inheritance relationships for classes, interfaces and traits
 * Generates class properties & method signatures, including type hints from @var doc comments
 * Dependencies are inferred from constructor argument types (assumes dependency injection)
 * Associations are inferred from "new" expressions (\<\<creates\>\>) and "throw" statements (\<\<throws\>\>)
 * Types renderable with fully qualified or short names (independently configurable for interfaces, traits, classes, properties and method arguments)   
 * Works on Linux (tested), Windows (tested), macOS (probably)

## Installation

The easiest way to install PhPuml is as a composer project.

```bash
$ composer create-project jhofm/ph-puml
```

A [phar](https://www.php.net/manual/en/book.phar.php) version of the tool is created a part of the build process, but currently not reliably downloadable. However with the project already installed,
you can create the phar yourself using [clue/phar-composer](https://github.com/clue/phar-composer).  
```bash
$ curl -JL -o phar-composer.phar https://clue.engineering/phar-composer-latest.phar
$ php phar-composer.phar ph-puml
$ php ph-puml.phar
```

## Quick Start

The `ph-puml` script will output PlantUML syntax describing all PHP files found in the current folder when run without any parameters. 

```bash
$ cd mycode/src
$ ph-puml 
```

You can specify a relative or absolute path to a target directory or file as the first argument.

```bash
$ ph-puml mycode/src
```

The second optional argument is the output path. The console's standard output will be used if none is specified.

The following two commands produce the same result:
```bash
$ ph-puml mycode/src > class.puml
$ ph-puml mycode/src class.puml
```

## Advanced features

### Output formats

PhPuml generates PlantUML puml file syntax by default, but you can also export most output formats supported by PlantUML directly.

Currently, these are:
 - eps (Postscript)
 - latex (LaTeX/Tikz)
 - latex:nopreamble (LaTeX/Tikz without preamble)
 - png (PNG image)
 - svg (SVG vector image)
 - scxml (SCXML state chart, seems broken in PlantUML Version 1.2020.26)
 - txt (ASCII art)
 - utxt (ASCII art with unicode letters)
 - vdx (VDX image)
 - xmi (XMI metadata description)

This requires a Java Runtime Environment on the machine running PhPuml. See the [PlantUML guide](https://plantuml.com/starting) for more information.
You also need to either:

- provide a path to a `plantuml.jar` file

```bash
$ ph-puml /my/code/dir -p /somedir/plantuml.jar -f svg > ~/mycode.svg
```

 - or install the optional [jawira/plantuml](https://packagist.org/packages/jawira/plantuml) package

```bash
$ composer create-project jhofm/ph-puml
$ cd ph-puml
$ composer require jawira/plantuml
$ ph-puml /my/code/dir -f svg > ~/mycode.svg
```

### Path filters

If the input path is a directory, PhPuml will determine the code files to analyze using a set of inclusion and exclusion rules.
By default, files in the directory tree with the file extension `.php` are included, as long as none of their parent folders are called `vendor`.
 
You can override the filter rules with command line options. All rules are regular expressions. You can use several at the same time.
For example the following command will NOT skip files from `vendor` folders, and analyze files in the `includes` folder with the file extension `.inc` as well.  

```bash
$ ph-puml -e -i "/\.php$/" -i "/^includes/.*\.inc$/"
```

The command will fail when attempting to parse files that do not contain valid PHP code.

### Namespaces

Namespaced classes, interfaces and traits are rendered with fully qualified names by default, while property and method argument types are not. 
This behaviour can be customized using the `namespaced-types`/`t` option.

```bash
$ ph-puml -t cmp # render all types as FQNs
$ ph-puml -t # render short types only
$ ph-puml -t c # render only classlikes (classes, interfaces & traits) as FQNs  
```

### Relations to external types

Relations to types that have not been analyzed are not rendered by default to reduce clutter in the generated diagram.
This included built-in Types like \Exception etc. Add the following option to include these relations. 

```bash
$ ph-puml -x false # include relations to types that were not analyzed
```

### Help   
PhPuml uses `symfony/command`, so a help page including all supported arguments and options is available.   

```bash
$ ph-puml -h
```

Omitting namespaces for classes, interfaces or traits may cause relations to be drawn wrong if unqualified type names are repeated in the analyzed sources.

## Limitations

* Auto generated class diagrams will probably never exactly meet your needs, but provide a starting point for manual refinement (and save mind-numbing work).
* PhPuml is able to handle huge amounts of code files, but limiting diagrams to as few classes as needed is always good idea.
* Cleaner code will yield better results. Type hints and Namespaces help a whole lot, for example.
* There's a lot of polishing still to be done, like inferring additional relation types or providing more customisations. 

## Troubleshooting

* `Uncaught Error: Class Composer\InstalledVersions not found`: PhPuml requires Composer 2
* `require(): Failed opening required ...`: Run composer install 

## Acknowledgements

This would have been exponentially more difficult to do without [Nikita Popov](https://github.com/nikic)'s [PHP-Parser](https://github.com/nikic/PHP-Parser),
so many thanks for that. [Symfony](https://github.com/symfony) helped a lot, too. 
Basically every dependency i use is maintained by kings, queens and total legends. <3  

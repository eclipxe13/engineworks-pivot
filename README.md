# eclipxe13/engineworks-pivot

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Scrutinizer][badge-quality]][quality]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]
[![SensioLabsInsight][badge-sensiolabs]][sensiolabs]

Use this library to retrieve a pivot table (aka dynamic table) from a database.

The pivot allows you to define a source (a table, a view or a query), source fields, filters, rows and columns
to group by and aggregators to operate (like sum, average, count, standar deviation...).
All this pivot structure can be stored and loaded in xml files and modified at runtime.

After a pivot structure has been set, you can query the database for the results and receive a organized information
no navigate. The library includes a formatter helper class to create an XHTML Table based on a query result.

## Installation

Use composer to install this library `composer require eclipxe/engineworks-pivot`

## Contributing

Contributions are welcome! Please read [CONTRIBUTING][] for details
and don't forget to take a look in the [TODO][] and [CHANGELOG][] files.

## Copyright and License

The eclipxe13/engineworks-pivot library is copyright © [Carlos C Soto](https://eclipxe.com.mx/)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/eclipxe13/engineworks-pivot/blob/master/CONTRIBUTING.md
[changelog]: https://github.com/eclipxe13/engineworks-pivot/blob/master/CHANGELOG.md
[todo]: https://github.com/eclipxe13/engineworks-pivot/blob/master/TODO.md

[source]: https://github.com/eclipxe13/engineworks-pivot
[release]: https://github.com/eclipxe13/engineworks-pivot/releases
[license]: https://github.com/eclipxe13/engineworks-pivot/blob/master/LICENSE
[build]: https://travis-ci.org/eclipxe13/engineworks-pivot?branch=master
[quality]: https://scrutinizer-ci.com/g/eclipxe13/engineworks-pivot/?branch=master
[sensiolabs]: https://insight.sensiolabs.com/projects/4b37e632-0a61-4e6c-940b-9bf0ad906e27
[coverage]: https://scrutinizer-ci.com/g/eclipxe13/engineworks-pivot/code-structure/master
[downloads]: https://packagist.org/packages/eclipxe/engineworks-pivot

[badge-source]: http://img.shields.io/badge/source-eclipxe13/engineworks--templates-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/github/release/eclipxe13/engineworks-pivot.svg?style=flat-square
[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[badge-build]: https://img.shields.io/travis/eclipxe13/engineworks-pivot/master.svg?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/eclipxe13/engineworks-pivot/master.svg?style=flat-square
[badge-sensiolabs]: https://insight.sensiolabs.com/projects/4b37e632-0a61-4e6c-940b-9bf0ad906e27/mini.png
[badge-coverage]: https://img.shields.io/scrutinizer/coverage/g/eclipxe13/engineworks-pivot/master.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/eclipxe/engineworks-pivot.svg?style=flat-square

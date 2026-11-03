# eclipxe13/engineworks-pivot - PHP Pivot (dynamic tables)

[![Source Code][badge-source]][source]
[![Packagist PHP Version Support][badge-php-version]][php-version]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Reliability][badge-reliability]][reliability]
[![Maintainability][badge-maintainability]][maintainability]
[![Code Coverage][badge-coverage]][coverage]
[![Violations][badge-violations]][violations]
[![Total Downloads][badge-downloads]][downloads]

Use this library to retrieve a pivot table (aka dynamic table) from a database.

The pivot allows you to define a source (a table, a view or a query), source fields, filters, rows and columns
to group by and aggregators to operate (like sum, average, count, standar deviation...).
All this pivot structure can be stored and loaded in xml files and modified at runtime.

After a pivot structure has been set, you can query the database for the results and receive organized information
to navigate. The library includes a formatter helper class to create an XHTML Table based on a query result.

## Installation

Use composer to install this library `composer require eclipxe/engineworks-pivot`

## Contributing

Contributions are welcome! Please read [CONTRIBUTING][] for details
and don't forget to take a look in the [TODO][] and [CHANGELOG][] files.

## Copyright and License

The eclipxe13/engineworks-pivot library is copyright © [Carlos C Soto](https://eclipxe.com.mx/)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/eclipxe13/engineworks-pivot/blob/master/CONTRIBUTING.md
[changelog]: https://github.com/eclipxe13/engineworks-pivot/blob/master/docs/CHANGELOG.md
[todo]: https://github.com/eclipxe13/engineworks-pivot/blob/master/docs/TODO.md

[source]: https://github.com/eclipxe13/engineworks-pivot
[php-version]: https://packagist.org/packages/eclipxe/engineworks-pivot
[release]: https://github.com/eclipxe13/engineworks-pivot/releases
[license]: https://github.com/eclipxe13/engineworks-pivot/blob/main/LICENSE
[build]: https://github.com/eclipxe13/engineworks-pivot/actions/workflows/build.yml?query=branch:master
[reliability]:https://sonarcloud.io/component_measures?id=eclipxe13_engineworks-pivot&metric=Reliability
[maintainability]: https://sonarcloud.io/component_measures?id=eclipxe13_engineworks-pivot&metric=Maintainability
[coverage]: https://sonarcloud.io/component_measures?id=eclipxe13_engineworks-pivot&metric=Coverage
[violations]: https://sonarcloud.io/project/issues?id=eclipxe13_engineworks-pivot&resolved=false
[downloads]: https://packagist.org/packages/eclipxe/engineworks-pivot

[badge-source]: https://img.shields.io/badge/source-eclipxe13/engineworks--pivot-blue?logo=github
[badge-php-version]: https://img.shields.io/packagist/php-v/eclipxe/engineworks-pivot?logo=php
[badge-release]: https://img.shields.io/github/release/eclipxe13/engineworks-pivot?logo=git
[badge-license]: https://img.shields.io/github/license/eclipxe13/engineworks-pivot?logo=open-source-initiative
[badge-build]: https://img.shields.io/github/actions/workflow/status/eclipxe13/engineworks-pivot/build.yml?branch=master&logo=github-actions
[badge-reliability]: https://sonarcloud.io/api/project_badges/measure?project=eclipxe13_engineworks-pivot&metric=reliability_rating
[badge-maintainability]: https://sonarcloud.io/api/project_badges/measure?project=eclipxe13_engineworks-pivot&metric=sqale_rating
[badge-coverage]: https://img.shields.io/sonar/coverage/eclipxe13_engineworks-pivot/main?logo=sonarqubecloud&server=https%3A%2F%2Fsonarcloud.io
[badge-violations]: https://img.shields.io/sonar/violations/eclipxe13_engineworks-pivot/main?format=long&logo=sonarqubecloud&server=https%3A%2F%2Fsonarcloud.io
[badge-downloads]: https://img.shields.io/packagist/dt/eclipxe/engineworks-pivot?logo=packagist

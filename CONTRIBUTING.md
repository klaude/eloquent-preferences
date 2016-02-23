# How to contribute

Thanks for wanting to help! Seriously, that's awesome.

## Reporting bugs

Most of the time we don't know there's a problem unless someone tells us, so thanks a bunch for reporting bugs. Please [open an issue](https://github.com/klaude/eloquent-preferences/issues/new) describing the bug, when it started, and if anything changed in your project around the time it started. More information is always better. If possible please include the PHP and Eloquent versions you're using and if you're using Eloquent on its own or as part of the Laravel framework.

## Contributing code

[Pull requests](https://github.com/klaude/eloquent-preferences/pulls) are always welcome. Please keep the following in mind if you want to submit new work:

* This library targets currently supported versions of PHP and HHVM.
* This library targets Eloquent 5.0 and above and should work in the latest published 5.x minor versions.
* [.travis.yml](https://github.com/klaude/eloquent-preferences/blob/master/.travis.yml) is configured to test on all possible PHP/HHVM and Eloquent versions. If TravisCI builds succeed then that's good enough for me.
* New code should work with Eloquent both standalone and as part of the full Laravel framework. It requires [illuminate/support](https://github.com/illuminate/support) in addition to Eloquent, but don't assume that users will be using the full framework.
* New code should adhere to the currently accepted [PHP Standards Recommendations (PSRs)](http://www.php-fig.org/psr/).
* New code should include unit tests. Use the existing ones as a guideline, and try to shoot for 80% code coverage or more in your tests.

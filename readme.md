# User
Lib called to reduce hassle in projects with oauth2. Expected to be convenient solution for the purpose.

## usage
To check usage cases, please, refer to `tests/unit/Oauth2Test.php`, `tests/unit/GoogleAuthenticatorTest.php`. Those tests explain how Oauth2 should be used in your application.

# Development
## install env
```
docker build -t lib-oauth2-client .
docker run --rm -it -u $(id -u):$(id -g) -w /tmp -v ${PWD}:/tmp lib-oauth2-client composer i
```
## testing
```
docker run --rm -it -u $(id -u):$(id -g) -w /tmp -v ${PWD}:/tmp lib-oauth2-client vendor/bin/phpunit
```
## measuing coverage
```
docker run --rm -it -u $(id -u):$(id -g) -w /tmp -v ${PWD}:/tmp lib-oauth2-client php -dpcov.enabled=1 -dpcov.directory=/tmp vendor/bin/phpunit --coverage-text
```

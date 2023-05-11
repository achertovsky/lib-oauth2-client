# User
Lib called to reduce hassle in projects with oauth2. Expected to be convenient solution for the purpose.

## usage
tba

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

# Dailymotion - JC Test - Registration API


1. [Technical Stack](#technical_stack)
2. [How to build and launch containers](#containers)
3. [Services](#services)
    1. [Registration Service](#services_registration)
    2. [Activation Service](#services_activation)
4. [Tests](#tests)
5. [Architecture Diagrem](#architecture)

## Technical Stack<a name="technical_stack"></a>
- PHP 7.4
- Symfony LTS 4.4  framework with components: Router, Dependency Injection, Event Dispatcher, dBal (only for DB Connection, no ORM), Twig (for email template)
- PostgreSQL
- NGINX

## How to build and launch containers<a name="containers"></a>
```
git clone https://github.com/HooK81/dm_jc_test_registration_api.git
cd dm_jc_test_registration_api
docker-compose build
docker-compose up -d
docker-compose exec php composer build
```
### Changing the default HTTP port
Default HTTP port used is 8080.  
It is configurable in **docker-compose.yml** file.

1. Edit file **docker-compose.yml** with your favorite text editor
Replace 8080 by the port of your choice

3. Restart containers
```
docker-compose down 
docker-compose up -d
```

### DB Storage
No volume is configured for DBMS. So all data are lost when container is removed.

## Services<a name="services"></a>
### Registration Service<a name="services_registration"></a>


#### About email
The registration email containing the activation code is not actualy send.  
It is logged into container's STDOUT.

You can retrieve it in **jc_php** container logs with following command:
```
docker logs --tail 20 -f jc_php
```
The log about activation code email looks like :
```
[2021-01-21 21:28:40] mailer.INFO: Activation code for email@address.fr is 5302
```

#### Call Service
The registration service is available on following endpoint:  
**[POST]: http://127.0.0.1:8080/api/users/v1/register**

A valid cURL example is : 
```
curl -i -X POST \
   -H "Content-Type:application/json" \
   -d \
'{
  "email": "john@doe.com",
  "password": "password"
}' \
 'http://127.0.0.1:8080/api/users/v1/register'
```

Reponse will be something like :
```json
HTTP 200/OK
{
  "id": 1,
  "email": "john@doe.com",
  "registered_at": "2021-01-21 21:28:40",
  "activated": false,
  "activation_code_expire_at": "2021-01-21 21:29:40"
}
```

### Activation Service<a name="services_activation"></a>

#### Call Service
The activation service is available on following endpoint:  
**[PUT]: http://127.0.0.1:8080/api/users/v1/activate/{activation_code}**  
**CAUTION**: HTTP Basic Auth is required

A valid cURL example is : 
```
curl -i -X PUT -v -u john@doe.com:password \
   'http://127.0.0.1:8080/api/users/v1/activate/CODE'
```
Be sure to replace, **EMAIL** and **PASSWORD** with values provided in registration service.  
Also replace **CODE** by activation code.

Reponse will be something like :
```json
HTTP 200/OK
{
  "id": 1,
  "email": "john@doe.com",
  "registered_at": "2021-01-21 21:28:40",
  "activated": true,
  "activated_at": "2021-01-21 21:29:05"
}
```

# Running tests<a name="tests"></a>

To run tests, you can use following command:
```
docker-compose exec php composer tests
```

With HTML coverage report
```
docker-compose exec php composer tests-coverage
```
The HTML report should be generated into a folder named **coverage**  

The current test suite contains **93 tests** with **211 assertions** for **100% code coverage**.

# Architecture Diagram<a name="architecture"></a>

![Architecture Diagram](https://raw.githubusercontent.com/HooK81/dm_jc_test_registration_api/master/architecture/architecture.svg?sanitize=true)

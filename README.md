
# REST API with Lumen 6.0

A RESTful API for Lumen micro-framework. Features included:

- Users Resource
- OAuth2 Authentication
- Scope based Authorization
- Validation
- Register new user
- Update user info
- Forgot password
- Change password
- Send Activation code via MailGun
- Support multiple language
- Social Login: Google, 

## Getting Started
First, clone the repo:
```bash
$ git clone git@github.com:wakuki14/LUMEN-Rest-API.git
```

#### Install dependencies
```
$ cd LUMEN-Rest-API
$ composer install
```
#### Initialize database
```
php artisan migrate
```

#### Configure the Environment
Update database info, MailGun credential in `.env`


## License

 [MIT license](http://opensource.org/licenses/MIT)


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

## Getting Started
First, clone the repo:
```bash
$ git clone git@github.com:wakuki14/LUMEN-Rest-API.git
```

#### Laravel Homestead
You can use Laravel Homestead globally or per project for local development. Follow the [Installation Guide](https://laravel.com/docs/5.5/homestead#installation-and-setup).

#### Install dependencies
```
$ cd rest-api-with-lumen
$ composer install
```

#### Configure the Environment
Create `.env` file:
```
$ cat .env.example > .env
```
If you want you can edit database name, database username and database password.

## License

 [MIT license](http://opensource.org/licenses/MIT)

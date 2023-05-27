# Tickets-Heaven-PHP-Slim

Video: 

Zip: https://www.mediafire.com/file/f1zv09iomsm7gia/Tickets_Heaven.zip/file

Please download the above zip file in order to get everything working correctly.

Make sure to create a database called 'tickets-heaven' in your PMA and then run the following commands to create and seed the tables:

```
phinx migrate
phinx seed:run -s ContinentsDbInfo
phinx seed:run -s CountriesDbInfo
phinx seed:run -s CurrenciesDbInfo
phinx seed:run -s PhoneCodesDbInfo
```

Also make sure to create an .env file with the following format and update it with your details:

```
APP_URL=http://localhost
APP_NAME="Tickets Heaven"

APP_PROFILE_PICTURES_FOLDER=/uploads/profile-pictures/
APP_PROFILE_PICTURES_MAX_WIDTH=400
APP_PROFILE_PICTURES_MAX_HEIGHT=400

APP_VENUE_PICTURES_FOLDER=/uploads/venue-pictures/
APP_VENUE_PICTURES_MAX_WIDTH=600
APP_VENUE_PICTURES_MAX_HEIGHT=300

APP_EVENT_PICTURES_FOLDER=/uploads/event-pictures/
APP_EVENT_PICTURES_MAX_WIDTH=600
APP_EVENT_PICTURES_MAX_HEIGHT=300

APP_EXCHANGE_RATE_API_ENDPOINT=https://open.er-api.com/v6/latest/
APP_DEFAULT_CURRENCY=BGN

AUTH_REMEMBER=user_remember
AUTH_GITHUB_CLIENT_ID=
AUTH_GITHUB_CLIENT_SECRET=
AUTH_GITHUB_REDIRECT_URI=${APP_URL}/social/handle/github
AUTH_FACEBOOK_CLIENT_ID=
AUTH_FACEBOOK_CLIENT_SECRET=
AUTH_FACEBOOK_REDIRECT_URI=${APP_URL}/social/handle/facebook

DB_DRIVER=mysql
DB_HOST=
DB_PORT=3306
DB_NAME=
DB_USERNAME=
DB_PASSWORD=
DB_CHARSET=utf8
DB_COLLATION=utf8_unicode_ci
DB_PREFIX=

MAIL_HOST=smtp.mailtrap.io
MAIL_CHARSET=UTF-8
MAIL_SMTP_AUTH=true
MAIL_SMTP_SECURE=tls
MAIL_PORT=465
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_HTML=true
MAIL_SUPPORT=support@tickets-heaven.bg
MAIL_DISABLE=0
```

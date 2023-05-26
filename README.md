# Tickets-Heaven-PHP-Slim

Video: 

Zip: https://www.mediafire.com/file/f1zv09iomsm7gia/Tickets_Heaven.zip/file

Please download the above zip file in order to get everything working correctly.

Make sure to create a database called 'tickets-heaven' in your PMA and then run the following commands to create and seed the tables:

phinx migrate

phinx seed:run -s ContinentsDbInfo

phinx seed:run -s CountriesDbInfo

phinx seed:run -s CurrenciesDbInfo

phinx seed:run -s PhoneCodesDbInfo

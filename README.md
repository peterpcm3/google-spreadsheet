# Xml parser app
Console application for parsing local or remote xml files

## Install instructions

### Build and start docker containers

- cd docker
- docker-compose up --build -d

### App configuration
- If you would like to use local xml source, place it in the project var/data folder. The
project configuration in services.yaml have to be change to use
  folder source: **app.xml_path: '%kernel.project_dir%/var/data'**
  
- If you prefer to fetch the xml data from ftp file, then change the project configuration in services.yaml
to user **app.xml_path: 'ftp://{username}:{password}@{domain}/' as parameter**
  
- Rename .env_example to .env

- To authenticate the google client, place the google application OAuth credentials in **config/googletoken.json**

### App run
To run the console application run:
```php bin/console app:parse-xml coffee_feed.xml```
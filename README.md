# HomonculusMVC
<img src="https://i.imgur.com/ZtXNYCM.png" alt="I-Colony"/>  

Homonculus is a boiler plate project for content managment that utilises the Model View Controller(MVC) development approach

## Installation
The project runs a [LAMP] stack. Ensure you have MySQL 5.6.x, APACHE 2.4 and PHP 7.x installed on your machine before following the instructions
below.

- Clone, add or pull the repo locally to your **www** or **htdocs** folder
```git
git clone https://github.com/Gokuzimaki/icolony.git
```
- If this is a first time setup, on your phpmyadmin area or MySQL Terminal window, import the **homonculus.sql** in the **root** folder. This will create the requisite **homonculusmvc** database and tables.


- Install [composer](https://getcomposer.org/doc/00-intro.md) and from your terminal window navigate to **app/modules** the composer **vendors**
 folder is here, run the following command to install all project external dependecies:
 ```sh
 $ composer update
 ```
 
- In the **snippets** folder, open up the **connection.php** file and setup the following variables:
```php

// Database setup variables
$hostname_pvmart = "localhost";
$db = "homonculus";
$username_pvmart = "yourusername";
$password_pvmart = "yourpassword"; 
```

- Extract all ***.rar*** files in the ***compressed*** folder to the root directory

- Test your local installation by starting your local web server and visiting **localhost/homonculusmvc** from your browser. If all is well you would have a nice welcome page displayed.

- You can also test your installation using [**ngrok**](https://ngrok.com/) to tunnel your localhost to the internet granting it a public address, simply [install](https://ngrok.com/download) ngrok and run the following command:
```sh
$ ngrok http -bind-tls=true 80
```
Then visit the ngrok exposed url in your browser to access icolony e.g "***https://690e174f7a47.ngrok.io/homonculus/***" and watch it open up lively from the web. 


### Todo
- Further documentation on folder and key file details and processes

[LAMP]: <https://en.wikipedia.org/wiki/LAMP_%28software_bundle%29>

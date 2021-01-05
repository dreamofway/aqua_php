# AQUA Framework
## htaccess 설정
```
DirectoryIndex index.php
RewriteEngine on                       
RewriteCond $1 !^(index\.php|(.*)\.swf|forums|images|css|downloads|js|robots\.txt|favicon\.ico)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ ./index.php?$1 [L,QSA] 
```
## 실행 순서
1. index.php
2. /aqua/_system/aqua.php 
3. /aqua/_system/class.aqua.php 
4. /aqua/controllers/개발자 컨트롤러

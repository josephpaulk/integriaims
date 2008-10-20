all: es.mo
	xgettext `find ../../ -name "*\.php" 2>/dev/null ! -name config.php` --output=index.pot  --keyword=__ --add-comments=///
	
es.mo: es.po
	msgfmt es.po -o es.mo


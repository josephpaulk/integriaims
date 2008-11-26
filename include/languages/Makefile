all: da.mo  es.mo  ko.mo
	xgettext `find ../../ -name "*\.php" 2>/dev/null ! -name config.php` --output=index.pot  --keyword=__ --add-comments=///

da.mo: da.po
	msgfmt da.po -o da.mo
	
es.mo: es.po
	msgfmt es.po -o es.mo
	
ko.mo: ko.po
	msgfmt ko.po -o ko.mo


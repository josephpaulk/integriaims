all: da.mo  el.mo  es.mo  ko.mo
	xgettext `find ../../ -name "*\.php" 2>/dev/null ! -name config.php` --output=index.pot  --keyword=__ --add-comments=///

da.mo: da.po
	msgfmt da.po -o da.mo
	
el.mo: el.po
	msgfmt el.po -o el.mo

es.mo: es.po
	msgfmt es.po -o es.mo
	
ko.mo: ko.po
	msgfmt ko.po -o ko.mo


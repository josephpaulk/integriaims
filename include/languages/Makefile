all: bn.mo  da.mo  el.mo  es.mo  fr.mo  ko.mo  tr.mo
	xgettext `find ../../ -name "*\.php" 2>/dev/null ! -name config.php` --output=index.pot  --keyword=__ --add-comments=///

bn.mo: bn.po
	msgfmt bn.po -o bn.mo
	
da.mo: da.po
	msgfmt da.po -o da.mo
	
el.mo: el.po
	msgfmt el.po -o el.mo

es.mo: es.po
	msgfmt es.po -o es.mo

fr.mo: fr.po
	msgfmt fr.po -o fr.mo

ko.mo: ko.po
	msgfmt ko.po -o ko.mo

tr.mo: tr.po
	msgfmt tr.po -o tr.mo

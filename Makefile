# Makefile

PHPCS=vendor\bin\phpcs.bat
PHPCBF=vendor\bin\phpcbf.bat
STANDARD=phpcs.xml

# Shortcut for running PHP Code Sniffer
lint:
	-$(PHPCS) --standard=$(STANDARD)  --ignore=vendor/,storage/,boostrap/,cofig/ .

# Shortcut for automatically fixing code standard violations
lint-fix:
	$(PHPCBF) --standard=$(STANDARD) --ignore=vendor/,storage/,boostrap/,cofig/ .
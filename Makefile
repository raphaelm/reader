# POT file
pot:
				xgettext *.php -o i18n/template.pot -L PHP --package-name=gfreader --copyright-holder=raphael@geeksfactory.de -n --from-code=utf-8
locales:
				msgfmt -o i18n/en_GB/LC_MESSAGES/reader.mo i18n/en_GB/LC_MESSAGES/reader.po -f
				msgfmt -o i18n/eo/LC_MESSAGES/reader.mo i18n/eo/LC_MESSAGES/reader.po -f





TARGET=/srv/snap/

push:
	rsync -rzu $$(pwd)/ 188.166.13.50:$(TARGET)

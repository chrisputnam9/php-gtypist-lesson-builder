#!/bin/bash

name="john1"
chapter1_path="$HOME/dev/personal/gtypist-scripture-bible/build/script-poc/$name"

clear
./php-gtypist-lesson-builder create "$chapter1_path.txt" --verbose

cat "$chapter1_path.typ"

echo
echo "===================================================="
echo "Continue to test in gtypist? (y/n)"
read -r -n1 yn
echo
if [ "$yn" != "y" ]; then
	exit 0
fi

gtypist "$chapter1_path.typ"

echo
echo "===================================================="
echo "Continue to install & final test? (y/n)"
read -r -n1 yn
echo
if [ "$yn" != "y" ]; then
	exit 0
fi

sudo mv "$chapter1_path.typ" /usr/share/gtypist/
gtypist "$name.typ"

#!/bin/bash

chapter1_path="$HOME/dev/personal/gtypist-scripture-bible/build/script-poc/john_chapter_1"

clear
./php-gtypist-lesson-builder create "$chapter1_path.txt" --verbose

cat "$chapter1_path.typ"

gtypist "$chapter1_path.typ"

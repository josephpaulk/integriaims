#!/bin/bash
# Automatic processing of icons for avatar creation 

lista=`ls people*.png | grep -o "[a-z0-9\_]*" | grep -v "png" `
for a in $lista 
do

echo "Processing $a.png"
convert -resize 22x22 $a".png" $a".png"

done	


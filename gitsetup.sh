#!/bin/bash

echo "The script will setup the necessary git options."
echo "Complete the following steps:"
read -r -p "Name: " name
read -r -p "Email: " email
git config user.name "$name"
git config user.email "$email"
ssh-keygen -t ed25519 -C "$email" -f ~/.ssh/id_ed25519
echo "Your public key is the following:"
cat ~/.ssh/id_ed25519.pub

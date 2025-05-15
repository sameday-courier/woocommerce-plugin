# Pasul 2: Identifică fișierele cu modificări doar de permisiuni
FILES_WITH_PERMISSION_CHANGES=$(git diff --summary master | grep 'mode change' | awk '{print $NF}')

# Pasul 3: Salvează într-un fișier temporar
echo "$FILES_WITH_PERMISSION_CHANGES" > /tmp/permission_only_files.txt

# Pasul 4: Pentru fiecare fișier, copiază permisiunile din master
while IFS= read -r file; do
  # Obține permisiunile fișierului din master
  MASTER_PERMISSION=$(git ls-tree master "$file" | awk '{print substr($1,length($1)-3,3)}')

  # Dacă permisiunea e 755, setează executabil, altfel 644
  if [ "$MASTER_PERMISSION" = "755" ]; then
    chmod 755 "$file"
  else
    chmod 644 "$file"
  fi

  # Adaugă fișierul pentru commit
  git add "$file"
done < /tmp/permission_only_files.txt

# Pasul 5: Commit-ează modificările dacă există
if [ -s /tmp/permission_only_files.txt ]; then
  git commit -m "Sync file permissions with master"
  git push origin numele-branch-ului-tău
  echo "Permisiunile au fost sincronizate cu master și modificările au fost publicate."
else
  echo "Nu s-au găsit fișiere cu modificări exclusive de permisiuni."
fi
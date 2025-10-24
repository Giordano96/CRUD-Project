import pandas as pd
import re

# Percorsi dei file
csv_path = "recipes_ingredients_parsed_filtered2.csv"
tags_path = "lista_completa degli ingredienti.txt"

# Legge la lista di ingredienti (tags)
with open(tags_path, "r", encoding="utf-8") as f:
    tags = [line.strip().lower() for line in f.readlines() if line.strip()]

# Crea una mappa tag → possibili varianti (singolare/plurale)
def generate_variants(tag):
    variants = {tag}
    if tag.endswith("y"):
        variants.add(tag[:-1] + "ies")  # e.g. "berry" → "berries"
    elif tag.endswith(("s", "x", "z", "ch", "sh")):
        variants.add(tag + "es")  # e.g. "dish" → "dishes"
    else:
        variants.add(tag + "s")   # e.g. "apple" → "apples"
    return variants

tag_variants = {tag: generate_variants(tag) for tag in tags}

# Carica il CSV originale
df = pd.read_csv(csv_path)

# Funzione per trovare i tag presenti negli ingredienti
def estrai_tags(ingredient_text):
    ingredient_text = str(ingredient_text).lower()
    trovati = set()

    for tag, variants in tag_variants.items():
        # Usa regex per evitare falsi positivi (match su parole intere)
        for v in variants:
            if re.search(rf"\b{re.escape(v)}\b", ingredient_text):
                trovati.add(tag)
                break  # evita doppioni se sia "apple" che "apples" compaiono
    return ", ".join(sorted(trovati))

# Applica la funzione per creare la nuova colonna 'tags'
df["tags"] = df["ingredients_parsed"].apply(estrai_tags)

# Salva il nuovo file CSV
output_path = "ingredients_with_tags.csv"
df.to_csv(output_path, index=False)

print(f"✅ File salvato come: {output_path}")

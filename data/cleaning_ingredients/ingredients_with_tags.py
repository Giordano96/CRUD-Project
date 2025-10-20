import pandas as pd

# Percorsi dei file
csv_path = "recipes_ingredients_parsed_filtered2.csv"  # Il tuo CSV originale
tags_path = "lista_completa degli ingredienti.txt"  # File con la lista di ingredienti/tag

# Legge la lista di ingredienti (tags)
with open(tags_path, "r", encoding="utf-8") as f:
    tags = [line.strip().lower() for line in f.readlines() if line.strip()]

# Carica il CSV originale
df = pd.read_csv(csv_path)

# Funzione per trovare i tag presenti negli ingredienti
def estrai_tags(ingredient_text):
    ingredient_text = str(ingredient_text).lower()
    trovati = [tag for tag in tags if tag in ingredient_text]
    return ", ".join(sorted(set(trovati)))

# Applica la funzione per creare la nuova colonna 'tags'
df["tags"] = df["ingredients_parsed"].apply(estrai_tags)

# Salva il nuovo file CSV
output_path = "ingredients_with_tags.csv"
df.to_csv(output_path, index=False)

print(f"✅ File salvato come: {output_path}")

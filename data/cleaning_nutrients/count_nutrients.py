import pandas as pd
import re

# Carica il file CSV
df = pd.read_csv("recipes_cleaned.csv")

# Funzione per estrarre i nutrienti da nutrition
def extract_nutrients(nutrition_string):
    if pd.isna(nutrition_string):
        return []
    # Trova tutte le occorrenze di nutrienti che terminano con ':'
    nutrients = re.findall(r"(\w+\s*\w*):", nutrition_string)
    return nutrients

# Estrai tutti i nutrienti dalla colonna nutrition
all_nutrients = []
for nutrition in df["nutrition"]:
    nutrients = extract_nutrients(nutrition)
    all_nutrients.extend(nutrients)

# Ottieni i valori unici e ordinali
unique_nutrients = sorted(set(all_nutrients))

# Stampa i valori unici
print("Nutrienti unici:")
for nutrient in unique_nutrients:
    print(f"- {nutrient}")
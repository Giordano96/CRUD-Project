import pandas as pd

# Carica il file CSV
df = pd.read_csv("../cleaning_categories/recipes_cleaned.csv")

# Estrai i valori unici dalla colonna cuisine_path
unique_cuisine_paths = sorted(set(df["cuisine_path"].dropna()))

# Stampa i valori unici
print("Valori unici in cuisine_path:")
for cuisine in unique_cuisine_paths:
    print(f"- {cuisine}")
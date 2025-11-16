import pandas as pd
import re

# Carica il file CSV
df = pd.read_csv("recipes_cleaned.csv")

# Funzione per estrarre le unità di misura da ingredients_parsed
def extract_units(parsed_string):
    # Trova tutte le occorrenze di "unit: valore"
    units = re.findall(r"unit: (\w+)", parsed_string)
    return units

# Applica la funzione a ogni riga della colonna ingredients_parsed
all_units = []
for parsed in df["ingredients_parsed"]:
    if pd.notna(parsed):  # Controlla che il valore non sia NaN
        units = extract_units(parsed)
        all_units.extend(units)

# Ottieni i valori unici
unique_units = sorted(set(all_units))

# Stampa i valori unici
print("Unità di misura uniche:")
for unit in unique_units:
    print(f"- {unit}")
import pandas as pd
import re

# Carica il file CSV originale
input_file = 'recipes_ingredients_parsed.csv'
df = pd.read_csv(input_file)

# Stampa il numero di righe iniziali
print(f"Righe iniziali: {len(df)}")

# Elimina le righe dove ingredients_parsed è None o NaN
df_filtered = df.dropna(subset=['ingredients_parsed'])

# Stampa il numero di righe dopo la rimozione
print(f"Righe dopo la rimozione di None/NaN: {len(df_filtered)}")

# Salva il nuovo CSV filtrato
output_file = 'recipes_ingredients_parsed_filtered.csv'
df_filtered.to_csv(output_file, index=False)
print(f"Nuovo file CSV salvato come: {output_file}")


# Funzione per estrarre quantity e unit da ingredients_parsed
def extract_units_and_quantities(parsed_string):
    units = set()
    quantities = set()

    # Dividi la stringa in singole triplette (quantity, unit, ingredient)
    entries = parsed_string.split(' quantity: ')

    for entry in entries[1:]:  # Salta il primo elemento vuoto
        # Estrai quantity, unit e ingredient usando regex
        quantity_match = re.match(r'(\d*\.?\d*)\s+unit:\s+([^\s]+(?:\s+[^\s]+)*)\s+ingredient:', entry)
        if quantity_match:
            quantity = quantity_match.group(1)
            unit = quantity_match.group(2)
            # Aggiungi a insiemi per evitare duplicati
            quantities.add(float(quantity) if quantity else 0.0)  # Converti in float, gestendo casi vuoti
            units.add(unit)

    return units, quantities


# Inizializza insiemi per raccogliere tutte le unità e quantità uniche
all_units = set()
all_quantities = set()

# Itera su ogni riga della colonna ingredients_parsed del DataFrame filtrato
for parsed in df_filtered['ingredients_parsed']:
    units, quantities = extract_units_and_quantities(parsed)
    all_units.update(units)
    all_quantities.update(quantities)

# Stampa le unità uniche
print("\nUnità uniche (unit):")
for unit in sorted(all_units):
    print(unit)

# Stampa le quantità uniche
print("\nQuantità uniche (quantity):")
for quantity in sorted(all_quantities):
    print(quantity)
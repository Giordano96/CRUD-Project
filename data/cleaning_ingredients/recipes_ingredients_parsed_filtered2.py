import pandas as pd
import re

# Carica il file CSV originale
input_file = 'recipes_ingredients_parsed.csv'
df = pd.read_csv(input_file)

# Stampa il numero di righe iniziali
print(f"Righe iniziali: {len(df)}")

# Elimina le righe dove ingredients_parsed è None o NaN
df_filtered = df.dropna(subset=['ingredients_parsed'])

# Lista degli ingredienti da eliminare
ingredients_to_remove = [
    '/4 cup juice reserved',
    '1% milk',
    '1-pint canning jars with lids and rings',
    '1/2-inch cubes watermelon',
    '100 proof vodka',
    '13-ounce jar with lid',
    '151 proof rum (such as Bacardi®)',
    '2% reduced-fat milk',
    '70% dark chocolate',
    '9-inch pie crusts'
]

# Elimina le righe che contengono uno qualsiasi degli ingredienti specificati
df_filtered = df_filtered[
    ~df_filtered['ingredients_parsed'].str.contains('|'.join([re.escape(ing) for ing in ingredients_to_remove]),
                                                    case=False, na=False)]

# Stampa il numero di righe dopo la rimozione
print(f"Righe dopo la rimozione di None/NaN e ingredienti specificati: {len(df_filtered)}")

# Salva il nuovo CSV filtrato
output_file = 'recipes_ingredients_parsed_filtered2.csv'
df_filtered.to_csv(output_file, index=False)
print(f"Nuovo file CSV salvato come: {output_file}")


# Funzione per estrarre gli ingredienti da ingredients_parsed
def extract_ingredients(parsed_string):
    ingredients = set()

    # Dividi la stringa in singole triplette (quantity, unit, ingredient)
    entries = parsed_string.split(' quantity: ')

    for entry in entries[1:]:  # Salta il primo elemento vuoto
        # Estrai l'ingrediente usando regex
        ingredient_match = re.search(r'ingredient:\s+("?)(.+?)\1(?:\s+quantity:|$)', entry)
        if ingredient_match:
            ingredient = ingredient_match.group(2).strip()  # Prende il contenuto senza virgolette
            ingredients.add(ingredient)

    return ingredients


# Inizializza un insieme per raccogliere tutti gli ingredienti unici
all_ingredients = set()

# Itera su ogni riga della colonna ingredients_parsed del DataFrame filtrato
for parsed in df_filtered['ingredients_parsed']:
    ingredients = extract_ingredients(parsed)
    all_ingredients.update(ingredients)

# Stampa gli ingredienti unici
print("\nIngredienti unici (ingredient):")
for ingredient in sorted(all_ingredients):
    print(ingredient)
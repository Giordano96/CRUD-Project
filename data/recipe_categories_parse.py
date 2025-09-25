import pandas as pd
import re
df = pd.read_csv('recipes.csv')
# Funzione per estrarre la categoria principale
def extract_main_category(path):
    if pd.isna(path) or not isinstance(path, str) or path.strip() == '':
        return 'Unknown'  # Gestisce NaN, stringhe vuote o non-stringhe
    match = re.match(r'/([^/]+)/', path)
    return match.group(1) if match else 'Unknown'  # Restituisce la categoria o 'Unknown'

# Sovrascrivi la colonna 'cuisine_path'
df['cuisine_path'] = df['cuisine_path'].apply(extract_main_category)
df.to_csv('recipes.csv', index=False)

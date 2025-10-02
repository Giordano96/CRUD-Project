import pandas as pd
import re

# Carica il dataset
df = pd.read_csv('recipes.csv', index_col=0)

# Funzione per parsare total_time in minuti numerici
def parse_time(time_str):
    if pd.isna(time_str) or not isinstance(time_str, str) or time_str.strip() == "":
        return None
    total_min = 0
    # Cerca ore (es. "1 hrs" o "1 hr")
    hrs = re.search(r'(\d+)\s*hrs?', time_str, re.IGNORECASE)
    # Cerca minuti (es. "30 mins" o "30 min")
    mins = re.search(r'(\d+)\s*mins?', time_str, re.IGNORECASE)
    if hrs:
        total_min += int(hrs.group(1)) * 60
    if mins:
        total_min += int(mins.group(1))
    return total_min if total_min > 0 else None

# Applica parse_time per creare total_mins
df['total_mins'] = df['total_time'].apply(parse_time)

# Gestisci NaN in total_mins (imputa con mediana)
median_time = df['total_mins'].median()
df.fillna({'total_mins': median_time}, inplace=True)
df['total_mins'] = df['total_mins'].astype(int)  # Converti in intero

# Sanifica servings: estrai numero, converti in int, imputa NaN con mediana
df['servings'] = df['servings'].apply(lambda x: int(re.search(r'\d+', str(x)).group(0)) if re.search(r'\d+', str(x)) else None)
median_serv = df['servings'].median()
df.fillna({'servings': median_serv}, inplace=True)
df['servings'] = df['servings'].astype(int)

# Droppa colonne
df.drop(columns=['yield', 'prep_time', 'cook_time', 'timing', 'total_time'], inplace=True, errors='ignore')

# Mostra le prime righe con le nuove colonne
print(df[['recipe_name', 'total_mins']].head().to_string())
print(df.info())

# Salva il dataframe modificato in un nuovo file CSV
df.to_csv('recipes.csv', index=True)
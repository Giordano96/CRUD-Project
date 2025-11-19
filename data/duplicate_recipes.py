
"""
Rimuove i duplicati di 'recipe_name' da recipes_cleaned.csv
"""

import pandas as pd
from pathlib import Path

# ----------------------------------------------------------------------
# CONFIGURAZIONE
# ----------------------------------------------------------------------
CSV_PATH = Path('recipes_cleaned.csv')
BACKUP_SUFFIX = '.bak'  # sarà: recipes_cleaned.csv.bak

# ----------------------------------------------------------------------
# FUNZIONE PRINCIPALE
# ----------------------------------------------------------------------
def clean_csv_in_place(csv_path: Path) -> None:
    if not csv_path.is_file():
        print(f"Errore: file non trovato → {csv_path}")
        return


    print(f"Caricamento: {csv_path.name}")
    try:
        df = pd.read_csv(csv_path)
    except Exception as e:
        print(f"Errore lettura CSV: {e}")
        return

    total_before = len(df)
    print(f"Righe totali: {total_before:,}")

    if 'recipe_name' not in df.columns:
        print("Errore: colonna 'recipe_name' non presente.")
        return

    # Conta duplicati
    dup_counts = df['recipe_name'].value_counts()
    duplicates = dup_counts[dup_counts > 1]

    if duplicates.empty:
        print("Nessun duplicato trovato. Il file è già pulito.")
        return

    print(f"Duplicati trovati: {len(duplicates):,}")

    # Mostra i primi 10 duplicati
    print("\nDuplicati (prime 10 occorrenze):")
    print("-" * 55)
    for name, count in duplicates.head(10).items():
        print(f"{count:>3} ×  {name}")
    if len(duplicates) > 10:
        print(f"    ... e altri {len(duplicates) - 10}")

    # Rimuovi duplicati
    print("\nRimozione duplicati (mantenuta la prima occorrenza)...")
    df_clean = df.drop_duplicates(subset='recipe_name', keep='first').reset_index(drop=True)

    total_after = len(df_clean)
    removed = total_before - total_after

    print(f"Righe rimosse: {removed:,}")
    print(f"Righe finali: {total_after:,}")

    # Sovrascrivi il file originale
    try:
        df_clean.to_csv(csv_path, index=False)
        print(f"\nFile originale sovrascritto con successo:")
        print(f"   → {csv_path.name}")
    except Exception as e:
        print(f"Errore nel salvataggio: {e}")
        return

if __name__ == '__main__':
    clean_csv_in_place(CSV_PATH)
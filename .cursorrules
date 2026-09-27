# Reglas del Proyecto (Ingeniería de Requerimientos)

## Contexto del Repositorio
- Entregable del curso de Ingeniería de Requerimientos de TECSUP ("Modelo de negocio, Parte 3").
- El contenido principal es un documento LaTeX **`docs/Proyecto.tex`** (APA 7.ª edición, Español) junto con sus imágenes en `docs/media/`.
- El directorio `src/` se encuentra vacío; la aplicación Laravel/PHP es trabajo a futuro.

## Compilación y Verificación
- El PDF debe compilarse desde `docs/` ejecutando pdflatex **dos veces** (para actualizar el índice dinámico y las referencias cruzadas):
  `pdflatex -interaction=nonstopmode Proyecto.tex` (x2).
- **No hay herramientas LaTeX instaladas en este entorno**, por lo que los agentes o IAs no pueden compilar el PDF directamente. El usuario debe compilarlo localmente.
- No hacer commits de los archivos generados en la compilación (`*.pdf`, `.aux`, `.toc`, `.out`, `.log`, `.synctex.gz`). Ya están en el `.gitignore`.

## Edición de `docs/Proyecto.tex`
- **Formato general:** APA 7.ª edición. Usa fuente tipo Times (`mathptmx`), interlineado doble (`\doublespacing`), sangría de 0.5 pulgadas (`\parindent=0.5in`), y el número de página en la esquina superior derecha (incluso en la carátula).
- **Idioma:** Español.
- **Estructura (Títulos):** Los encabezados (`\section`, `\subsection`) no llevan numeración (configurado vía `titlesec`). El documento está estructurado en 7 **Capítulos** que corresponden lógicamente a los "Avances" del proyecto (Avance 1 al 6). Mantener siempre esta estructura de Capítulos.
- **Imágenes:** Los títulos (captions) de las tablas van **arriba**, y los de las figuras van **debajo**. Tienen formato específico (número en negrita, título en cursiva). Al incluir imágenes (`\includegraphics`), el archivo debe existir en `docs/media/` o fallará la compilación.
- **Tablas de Casos de Uso (CUN):**
  - Tienen un formato fijo de 2 columnas: `\begin{tabular}{|>{\bfseries\raggedright\arraybackslash}p{4cm}|>{\raggedright\arraybackslash}p{11.5cm}|}`.
  - Deben tener formato de **marco completo** (todas las celdas delimitadas con `|` y separadas por `\hline`).
  - **IMPORTANTE:** No usar `\rowcolor` ni `\columncolor` (`colortbl` genera conflictos de compilación con este tipo de tablas largas en este proyecto).
  - Al final de la tabla siempre va una nota: `\small \emph{Nota.} ...`.
- **Matrices de Trazabilidad:** Deben mantener también el formato de marco completo (usar `\hline` en lugar de `\toprule`, `\midrule`, etc., de `booktabs`).

## Mantenimiento del README.md
- El README debe mantenerse siempre con un tono **profesional y académico**.
- **No usar emojis** innecesarios en los encabezados ni en el cuerpo del texto.
- **No agregar enlaces directos al documento PDF** (`Proyecto.pdf`), ya que el repositorio sirve únicamente como control de versiones y evidencia del código fuente/documentación base, no para almacenar binarios finales.

## Flujo de trabajo y precauciones
- El repositorio puede sincronizarse o editarse externamente. Siempre re-lee el estado actual de los archivos antes de asumir que una edición previa se guardó correctamente.
- Mantén siempre el `README.md` sincronizado de manera formal cuando la estructura del documento LaTeX sufra cambios significativos.

## Git y Control de Versiones
- Como IA, debes generar los commits proactivamente en nombre del usuario después de realizar cambios y verificar que compilen o funcionen.
- Utiliza **siempre** el formato de "Conventional Commits" en español: `tipo(ámbito): descripción corta` (ejemplo: `feat(docs): se organiza el documento por capítulos`, `fix(tablas): se elimina el paquete colortbl para arreglar error`).
- Separa los commits lógicamente (no hagas un solo commit masivo si puedes hacer commits atómicos por archivo o funcionalidad).

# Reglas del Proyecto (Sistema de Inventario "Mundo del Juguete")

## Contexto del Repositorio
- Proyecto integrador de TECSUP para tres cursos: Ingeniería de Requerimientos y Diseño de Software, Base de Datos Avanzado y Desarrollo de Aplicaciones en Internet.
- La aplicación web Laravel se encuentra en la raíz del repositorio (`app/`, `config/`, `database/`, `resources/`, `routes/`, `tests/`).
- La documentación académica es el documento LaTeX **`docs/Proyecto.tex`** (APA 7.ª edición, Español), con diagramas TikZ en `docs/diagramas/`, tablas en `docs/tablas/` e imágenes en `docs/media/`.

## Aplicación Laravel
- **Stack:** PHP 8.4, Laravel 13 (Breeze con Blade, Tailwind CSS 3 y Alpine.js), MySQL 8.0, Vite y PHPUnit 12. Interfaz y mensajes en español (`lang/es`), zona horaria `America/Lima`.
- **Roles:** `administrador`, `vendedor`, `cajero` y `almacenero`. El acceso se controla con el middleware `role:` en `routes/web.php`; no existe registro público (las cuentas se crean en el módulo Usuarios).
- **Base de datos:** nunca editar una migración ya publicada; los cambios de esquema se hacen con una migración nueva. Las llaves foráneas usan `RESTRICT` (los registros con historial se dan de baja con el estado `inactivo`, no se eliminan).
- **Operaciones de stock y dinero** (cobro, recepción de compras, devoluciones) se ejecutan dentro de `DB::transaction` con `lockForUpdate()`.
- **Pruebas:** toda corrección o funcionalidad nueva debe llevar su prueba en `tests/`. Ejecutar `php artisan test` antes de cada commit; GitHub Actions las corre en SQLite y MySQL 8.0.
- Tras cambiar vistas o estilos, compilar con `npm run build` (la carpeta `public/build` no se versiona).

## Compilación y Verificación
- El PDF debe compilarse desde `docs/` ejecutando pdflatex **dos veces** (para actualizar el índice dinámico y las referencias cruzadas):
  `pdflatex -interaction=nonstopmode Proyecto.tex` (x2).
- Si el entorno no tiene LaTeX instalado, el agente debe indicarlo y el usuario compila el PDF localmente (por ejemplo, en Overleaf).
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

## Autonomía y Permisos (IA)
- **Permiso Total:** Tienes permiso absoluto ('permission of everything') para realizar cambios en los archivos, compilar, hacer commits y empujar cambios al repositorio. 
- **Proactividad:** No debes pedir permiso para realizar tus tareas. Ejecuta tus acciones y usa las herramientas directamente, informando al usuario únicamente sobre los resultados obtenidos.

## Git y Control de Versiones
- Como IA, debes generar los commits proactivamente en nombre del usuario después de realizar cambios y verificar que compilen o funcionen.
- Utiliza **siempre** el formato de "Conventional Commits" en español: `tipo(ámbito): descripción corta` (ejemplo: `feat(docs): se organiza el documento por capítulos`, `fix(tablas): se elimina el paquete colortbl para arreglar error`).
- Separa los commits lógicamente (no hagas un solo commit masivo si puedes hacer commits atómicos por archivo o funcionalidad).

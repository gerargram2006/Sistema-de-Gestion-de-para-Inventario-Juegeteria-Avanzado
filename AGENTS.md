# AGENTS.md

## What this repo is

A TECSUP course deliverable (Ingeniería de Requerimientos — "Modelo de negocio,
Parte 3"). The real content is a single LaTeX document, **`docs/Proyecto.tex`**
(APA 7-ª edición, Spanish), plus its images in `docs/media/`. `src/` is
currently **empty** — the Laravel/PHP app is future work described in the README,
not code to edit.

## Verification

- Compile from `docs/` with pdflatex, **twice** (dynamic TOC + cross-refs):
  `pdflatex -interaction=nonstopmode Proyecto.tex` (x2). `\usepackage{bookmark}`
  is already loaded, so the `.out` rerun warning is gone; the "rerun for
  cross-references" warning on first pass is expected.
- There is **no LaTeX toolchain in this environment** — agents cannot compile the
  PDF; ask the user to run the build locally.
- Never commit build output: `*.pdf`, `.aux`, `.toc`, `.out`, `.log`,
  `.synctex.gz`, etc. are gitignored.

## Editing `docs/Proyecto.tex`

- Text is Spanish; headings are unnumbered (APA style via `titlesec`).
- Captions sit **above** tables and below figures; number is bold + title in
  italic (`\captionsetup{labelsep=period, labelfont=bf, textfont=it}`).
- Use-case tables follow a fixed column spec:
  `>{\bfseries\raggedright\arraybackslash}p{4cm} >{\raggedright\arraybackslash}p{11.5cm}`
  and each rows a "Nota." line: `\small \emph{Nota.} ...`.
- `\includegraphics` may omit the file extension (e.g. `{media/image12}`). Any
  image referenced must exist in `docs/media/` or compilation fails.
- The doc is APA 7: Times-like font via `mathptmx`, double spacing,
  `\parindent=0.5in`, page number top-right on all pages including cover.

## Workflow gotchas

- The repository is edited/synced externally and sometimes changed between
  sessions. **Re-read current state before assuming a previous edit persisted**;
  duplicated blocks (e.g. a second `\tableofcontents`) have appeared after
  external syncs.
- README.md is the project's public documentation (contents, modelado,
  concluye/recomendaciones, creditos) — keep it in sync when the document's
  structure changes.
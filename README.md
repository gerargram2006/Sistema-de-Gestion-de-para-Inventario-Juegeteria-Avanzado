# 🧸 Sistema de Gestión de Inventario para Juguetería

Aplicación web desarrollada con **PHP + Laravel** para automatizar la gestión de
inventario de una juguetería minorista, aplicando los principios de la
**Programación Orientada a Objetos (POO)** bajo una arquitectura
**Modelo-Vista-Controlador (MVC)**.

## 🎯 Problema

El control manual de existencias y el uso de hojas de cálculo aisladas generan:

- Deficiencia en el **control de stock por categorías y marcas**.
- **Invisibilidad de existencias en tiempo real** (sin sincronización ventas ↔ almacén).
- **Ineficiencia** en procesos manuales e inventarios físicos.

## ✅ Solución

Una aplicación web modular, escalable y portable que centraliza las operaciones
en un solo sistema, garantizando agilidad, control preciso de existencias (stock
y precios), auditorías confiables y reportes para la toma de decisiones.

## 🏗️ Arquitectura

| Capa | Tecnología |
|------|------------|
| Lenguaje de servidor | PHP |
| Framework | Laravel (MVC) |
| Persistencia | Eloquent ORM + MySQL |
| Vistas | Blade + Bootstrap (HTML5, CSS, JavaScript) |

## 🗂️ Estructura del proyecto

```
docs/   Documento de investigación en formato APA 7.ª edición (LaTeX + PDF)
src/    Código fuente de la aplicación (Laravel)
```

## 📊 Modelo de dominio

**Entidades principales:** `Cliente`, `Producto`, `Usuario`, `Venta` y
`DetalleVenta`, organizadas en tablas maestras, gestión de inventario y núcleo
transaccional con integridad referencial y normalización.

## 🧑‍💻 Equipo

- Gerar Rodrigo Camma Baldeon
- Hugo Ernesto Florez Merma
- Barry Valdivia Lovon
- Samuel A. Llallacachi Salhua
- Simon Rodriguez Anculle

**Institución:** TECSUP · Arequipa, Perú — 2026 · 2.º Ciclo

**Curso:** Programación Orientada a Objetos — Docente: Luis Ignacio Diaz Bravo

## 🔗 Enlaces

- **Documento de investigación:** [`docs/Proyecto.pdf`](docs/Proyecto.pdf)
- **Repositorio GitHub:** <https://github.com/danielcabana-ship-it/Proyecto-Final-POO-Gesti-n-de-jugueter-a>
- **Video explicativo:** <https://youtu.be/TrAdfiT9CWs>
- **Recursos (Google Drive):** <https://drive.google.com/drive/folders/1gLkO0kbU5LngiZOESHCWmbuDCugcbQ-K?usp=drive_link>

## 📚 Referencias clave

- Welling, L. & Thompson, L. (2017). *PHP and MySQL web development* (5.ª ed.).
- Stauffer, M. (2019). *Laravel: Up and running* (2.ª ed.).
- Larman, C. (2004). *Applying UML and patterns* (3.ª ed.).
- PHP Documentation Group (2024). *Manual de PHP*.
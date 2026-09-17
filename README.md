# 🧸 Sistema de Gestión de Inventario para Juguetería

Proyecto de **Ingeniería de Requerimientos y Diseño de Software** — Entregable:
**Modelo de negocio (Parte 3)**. Aplica el modelado de negocio y los principios
de la **Programación Orientada a Objetos (POO)** para el diseño de una
plataforma de inventario de una juguetería minorista, con PHP + Laravel como
stack de implementación bajo arquitectura **Modelo-Vista-Controlador (MVC)**.

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

## 🧩 Modelado de negocio

**Casos de uso del negocio** (con precondiciones, secuencia, flujos
alternativos, postcondición y objetivo):

1. Registrar venta
2. Gestionar abastecimiento
3. Controlar inventario
4. Gestionar devoluciones y cambios

**Trabajadores del negocio:** vendedor, administrador/dueño, cajero y encargado
de almacén.

**Entidades del negocio:** usuarios, clientes, categorías, inventario de
productos, ventas, detalle\_ventas, devoluciones, compras y detalle\_compras.

**Diagramas elaborados:**

- Diagramas de clases por entidad (productos, ventas y detalle de ventas,
  compras y detalle de compras, devoluciones, usuarios y clientes).
- Diagramas de actividades por CUN (ventas, abastecimiento, inventario,
  devoluciones y cambios).
- Diagrama entidad-relación (ERD conceptual).

Los diagramas permitieron visualizar la participación de cada actor del negocio,
identificar puntos de riesgo (falta de stock, comprobantes inválidos, diferencias
en la recepción de mercancía) y orientar las validaciones y mensajes de error
del futuro sistema.

## 🧑‍💻 Equipo

- Gerar Rodrigo Camma Baldeon
- Hugo Ernesto Florez Merma
- Barry Valdivia Lovon
- Samuel A. Llallacachi Salhua
- Simon Rodriguez Anculle

**Institución:** TECSUP · Arequipa, Perú — 2026 · Ciclo III

**Curso:** Ingeniería de Requerimientos y Diseño de Software — Docente: Olanda Saavedra, Claudio

## 🔗 Enlaces

- **Documento de investigación:** [`docs/Proyecto.pdf`](docs/Proyecto.pdf)
- **Repositorio GitHub:** <https://github.com/gerargram2006/Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado.git>

## 📚 Referencias

- Banker, K., Bakkum, P., Verch, S., Garrett, D., y Hawkins, T. (2016). *MongoDB in action* (2.ª ed.). Manning Publications.
- Bradshaw, S., Brazil, E., y Chodorow, C. (2019). *MongoDB: The definitive guide* (3.ª ed.). O'Reilly Media.
- Pressman, R. S., y Maxim, B. R. (2021). *Ingeniería del software: Un enfoque práctico* (9.ª ed.). McGraw-Hill Education.
- Wiegers, K., y Beatty, J. (2013). *Software requirements* (3.ª ed.). Microsoft Press.
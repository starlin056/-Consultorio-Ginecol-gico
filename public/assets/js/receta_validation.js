// Validación del formulario de receta - VERSIÓN CORREGIDA
document.getElementById("consultaForm").addEventListener("submit", function(e) {
    const medicamentos = document.querySelectorAll(".medicamento-item");
    const analisis = document.querySelectorAll(".analisis-item");
    const tipoReceta = document.querySelector('input[name="tipo_receta"]:checked').value;
    
    // Si no hay ni medicamentos ni análisis, permitir enviar sin problemas (solo consulta)
    if (medicamentos.length === 0 && analisis.length === 0) {
        return; // No hay validación necesaria, solo se guarda la consulta
    }

    // Validar según el tipo de receta seleccionado
    if (tipoReceta === "medicamento") {
        // Solo validar medicamentos
        if (medicamentos.length === 0) {
            e.preventDefault();
            alert("Debe agregar al menos un medicamento cuando el tipo de receta es Medicamentos.");
            return;
        }

        let todosValidos = true;
        medicamentos.forEach((item, index) => {
            const inputs = item.querySelectorAll("[required]");
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = "var(--error)";
                    todosValidos = false;
                } else {
                    input.style.borderColor = "";
                }
            });
        });

        if (!todosValidos) {
            e.preventDefault();
            alert("Por favor complete todos los campos requeridos en los medicamentos");
            return;
        }

        // Si hay análisis pero el tipo es medicamento, preguntar si continuar
        if (analisis.length > 0) {
            if (!confirm("Ha seleccionado tipo 'Medicamentos' pero también ha agregado análisis. ¿Desea continuar guardando solo los medicamentos?")) {
                e.preventDefault();
                return;
            }
        }

    } else if (tipoReceta === "analisis") {
        // Solo validar análisis
        if (analisis.length === 0) {
            e.preventDefault();
            alert("Debe agregar al menos un análisis cuando el tipo de receta es Análisis.");
            return;
        }

        let todosValidos = true;
        analisis.forEach((item, index) => {
            const selects = item.querySelectorAll("select[required]");
            selects.forEach(select => {
                if (!select.value.trim()) {
                    select.style.borderColor = "var(--error)";
                    todosValidos = false;
                } else {
                    select.style.borderColor = "";
                }
            });
        });

        if (!todosValidos) {
            e.preventDefault();
            alert("Por favor complete todos los campos requeridos en los análisis");
            return;
        }

        // Si hay medicamentos pero el tipo es análisis, preguntar si continuar
        if (medicamentos.length > 0) {
            if (!confirm("Ha seleccionado tipo 'Análisis' pero también ha agregado medicamentos. ¿Desea continuar guardando solo los análisis?")) {
                e.preventDefault();
                return;
            }
        }
    }
});
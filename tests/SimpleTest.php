<?php
// tests/SimpleTest.php

// Un test muy básico que siempre pasa
function testBasic() {
    return true;
}

// Ejecutar el test
if (testBasic()) {
    echo "Test pasó correctamente.\n";
    exit(0); // 0 = éxito
} else {
    echo "Test falló.\n";
    exit(1); // 1 = error
}

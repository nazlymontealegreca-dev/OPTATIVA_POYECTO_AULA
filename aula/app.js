const STORAGE = "inventarioFET";

let datos = JSON.parse(localStorage.getItem(STORAGE)) || [
];

const tabla = document.getElementById("tablaBody");
const buscador = document.getElementById("buscador");
const estadoFiltro = document.getElementById("estadoFiltro");
const categoriaFiltro = document.getElementById("categoriaFiltro");

function guardar(){
    localStorage.setItem(STORAGE, JSON.stringify(datos));
}

function pintar(lista){

    if(lista.length === 0){
        tabla.innerHTML = `
        <tr>
            <td colspan="5" style="text-align:center;">No hay productos</td>
        </tr>`;
        actualizarContadores();
        return;
    }

    tabla.innerHTML="";

    lista.forEach((item,i)=>{

        let clase="";
        if(item.estado==="Disponible") clase="disponible";
        if(item.estado==="Prestado") clase="prestado";
        if(item.estado==="Mantenimiento") clase="mantenimiento";

        tabla.innerHTML+=`
        <tr>
            <td>${item.nombre}</td>
            <td>${item.categoria}</td>
            <td><span class="estado ${clase}">${item.estado}</span></td>
            <td>${item.cantidad}</td>
            <td>
                <i class="fa-solid fa-eye"></i>
                <i class="fa-solid fa-pen"></i>
                <i onclick="eliminar(${i})" class="fa-solid fa-trash"></i>
            </td>
        </tr>`;
    });

    actualizarContadores();
}

function actualizarContadores(){
    document.getElementById("total").innerText = datos.length || 0;
    document.getElementById("disp").innerText = datos.filter(x=>x.estado==="Disponible").length || 0;
    document.getElementById("prest").innerText = datos.filter(x=>x.estado==="Prestado").length || 0;
    document.getElementById("mant").innerText = datos.filter(x=>x.estado==="Mantenimiento").length || 0;
}

/* FILTRO */
function filtrar(){
    let texto = buscador.value.toLowerCase();
    let estado = estadoFiltro.value;
    let categoria = categoriaFiltro.value;

    let resultado = datos.filter(item=>{
        return item.nombre.toLowerCase().includes(texto) &&
        (estado==="Todos los Estados" || item.estado===estado) &&
        (categoria==="Todas las Categorías" || item.categoria===categoria);
    });

    pintar(resultado);
}

buscador.addEventListener("input", filtrar);
estadoFiltro.addEventListener("change", filtrar);
categoriaFiltro.addEventListener("change", filtrar);

/* AGREGAR */
document.querySelector(".btn-agregar").onclick=()=>{
    let n=prompt("Nombre");
    let c=prompt("Categoría");
    let e=prompt("Estado");
    let cant=prompt("Cantidad");

    if(n && c && e && cant){
        datos.push({nombre:n,categoria:c,estado:e,cantidad:parseInt(cant)});
        guardar();
        pintar(datos);
    }
};

/* ELIMINAR */
function eliminar(i){
    datos.splice(i,1);
    guardar();
    pintar(datos);
}

/* INICIO */
pintar(datos);
/* ADMIN DASHBOARD */

function cargarAdmin(){

    if(!document.getElementById("totalEquipos")) return;

    document.getElementById("totalEquipos").innerText = datos.length;

    document.getElementById("prestActivos").innerText =
        datos.filter(x=>x.estado==="Prestado").length;

    document.getElementById("atrasados").innerText =
        Math.floor(Math.random()*10); // simulado

    document.getElementById("mantAdmin").innerText =
        datos.filter(x=>x.estado==="Mantenimiento").length;
}

cargarAdmin();
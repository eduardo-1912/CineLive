package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Lugar {
    public String fila;
    public int numero, estado; // 0 = Disponível, 1 = Selecionado, 2 = Ocupado

    public Lugar(String fila, int numero, int estado) {
        this.fila = fila;
        this.numero = numero;
        this.estado = estado;
    }

    public String getFila() {return fila;}
    public int getNumero() {return numero;}
    public int getEstado() {return estado;}
}

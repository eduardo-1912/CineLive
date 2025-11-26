package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Bilhete {
    public int id;
    public String codigo, lugar, preco, estado;

    public Bilhete(int id, String codigo, String lugar, String preco, String estado) {
        this.id = id;
        this.codigo = codigo;
        this.lugar = lugar;
        this.preco = preco;
        this.estado = estado;
    }

    public int getId() {return id;}
    public String getCodigo() {return codigo;}
    public String getLugar() {return lugar;}
    public String getPreco() {return preco;}
    public String getEstado() {return estado;}
}

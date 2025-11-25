package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Compra {

    public int id;
    private final String tituloFilme, dataCompra, nomeCinema, estado, total, dataSessao, horaInicioSessao, lugares;

    public Compra(int id, String tituloFilme, String dataCompra, String nomeCinema, String estado, String total, String dataSessao, String horaInicioSessao, String lugares) {
        this.id = id;
        this.tituloFilme = tituloFilme;
        this.dataCompra = dataCompra;
        this.nomeCinema = nomeCinema;
        this.estado = estado;
        this.total = total;
        this.dataSessao = dataSessao;
        this.horaInicioSessao = horaInicioSessao;
        this.lugares = lugares;
    }

    public int getId() {return id;}
    public String getTituloFilme() {return tituloFilme;}
    public String getDataCompra() {return dataCompra;}
    public String getNomeCinema() {return nomeCinema;}
    public String getEstado() {return estado;}
    public String getTotal() {return total;}
    public String getDataSessao() {return dataSessao;}
    public String getHoraInicioSessao() {return horaInicioSessao;}
    public String getLugares() {return lugares;}
}

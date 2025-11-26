package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Compra {
    public int id;
    private final String tituloFilme, dataCompra, nomeCinema, nomeSala, estado, total, dataSessao, horaInicioSessao, horaFimSessao, lugares;

    public Compra(int id, String tituloFilme, String dataCompra, String nomeCinema, String nomeSala, String estado, String total, String dataSessao, String horaInicioSessao, String horaFimSessao, String lugares) {
        this.id = id;
        this.tituloFilme = tituloFilme;
        this.dataCompra = dataCompra;
        this.nomeCinema = nomeCinema;
        this.nomeSala = nomeSala;
        this.estado = estado;
        this.total = total;
        this.dataSessao = dataSessao;
        this.horaInicioSessao = horaInicioSessao;
        this.horaFimSessao = horaFimSessao;
        this.lugares = lugares;
    }

    public int getId() {return id;}
    public String getTituloFilme() {return tituloFilme;}
    public String getDataCompra() {return dataCompra;}
    public String getNomeCinema() {return nomeCinema;}
    public String getNomeSala() {return nomeSala;}
    public String getEstado() {return estado;}
    public String getTotal() {return total;}
    public String getDataSessao() {return dataSessao;}
    public String getHoraInicioSessao() {return horaInicioSessao;}
    public String getHoraFimSessao() {return horaFimSessao;}
    public String getLugares() {return lugares;}
}

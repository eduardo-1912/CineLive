package pt.ipleiria.estg.dei.amsi.cinelive.models;

import java.util.List;

public class Compra {
    private int id, sessaoId;
    private String data, total, estado, pagamento, tituloFilme, nomeCinema, nomeSala, dataSessao, horaInicioSessao, horaFimSessao, lugares;
    private List<String> lugaresSelecionados;

    // Create
    public Compra(int sessaoId, String pagamento, List<String> lugaresSelecionados) {
        this.sessaoId = sessaoId;
        this.pagamento = pagamento;
        this.lugaresSelecionados = lugaresSelecionados;
    }

    // Get
    public Compra(int id, String data, String total, String estado, String pagamento, String tituloFilme, String nomeCinema, String nomeSala, String dataSessao, String horaInicioSessao, String horaFimSessao, String lugares) {
        this.id = id;
        this.data = data;
        this.total = total;
        this.estado = estado;
        this.pagamento = pagamento;
        this.tituloFilme = tituloFilme;
        this.nomeCinema = nomeCinema;
        this.nomeSala = nomeSala;
        this.dataSessao = dataSessao;
        this.horaInicioSessao = horaInicioSessao;
        this.horaFimSessao = horaFimSessao;
        this.lugares = lugares;
    }

    public int getId() {return id;}
    public int getSessaoId() {return sessaoId;}
    public String getData() {return data;}

    public String getTituloFilme() {return tituloFilme;}
    public String getNomeCinema() {return nomeCinema;}
    public String getNomeSala() {return nomeSala;}
    public String getEstado() {return estado;}
    public String getTotal() {return total;}
    public String getDataSessao() {return dataSessao;}
    public String getHoraInicioSessao() {return horaInicioSessao;}
    public String getHoraFimSessao() {return horaFimSessao;}
    public String getLugares() {return lugares;}
    public String getPagamento() {return pagamento;}
    public List<String> getLugaresSelecionados() {return lugaresSelecionados;}
}

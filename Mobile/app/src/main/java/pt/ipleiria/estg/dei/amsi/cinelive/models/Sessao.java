package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Sessao {
    public int id;
    public String data, horaInicio, horaFim;

    public Sessao(int id, String data, String horaInicio, String horaFim) {
        this.id = id;
        this.data = data;
        this.horaInicio = horaInicio;
        this.horaFim = horaFim;
    }

    public int getId() {return id;}
    public String getData() {return data;}
    public String getHoraInicio() {return horaInicio;}
    public String getHoraFim() {return horaFim;}
}

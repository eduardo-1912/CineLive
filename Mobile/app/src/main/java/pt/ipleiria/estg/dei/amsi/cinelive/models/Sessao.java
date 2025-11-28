package pt.ipleiria.estg.dei.amsi.cinelive.models;

import java.util.List;

public class Sessao {
    private int id, numFilas, numColunas;
    private String nomeCinema, nomeSala, data, horaInicio, horaFim;
    private double precoBilhete;
    private List<String> lugaresOcupados;

    public Sessao(int id, String data, String horaInicio) {
        this.id = id;
        this.data = data;
        this.horaInicio = horaInicio;
    }

    public Sessao(int id, String nomeCinema, String nomeSala, String data, String horaInicio, String horaFim, double precoBilhete, int numFilas, int numColunas, List<String> lugaresOcupados) {
        this.id = id;
        this.nomeCinema = nomeCinema;
        this.nomeSala = nomeSala;
        this.data = data;
        this.horaInicio = horaInicio;
        this.horaFim = horaFim;
        this.precoBilhete = precoBilhete;
        this.numFilas = numFilas;
        this.numColunas = numColunas;
        this.lugaresOcupados = lugaresOcupados;
    }

    public int getId() {return id;}
    public String getNomeCinema() {return nomeCinema;}
    public String getNomeSala() {return nomeSala;}
    public String getData() {return data;}
    public String getHoraInicio() {return horaInicio;}
    public String getHoraFim() {return horaFim;}
    public double getPrecoBilhete() {return precoBilhete;}
    public int getNumFilas() {return numFilas;}
    public int getNumColunas() {return numColunas;}
    public List<String> getLugaresOcupados() {return lugaresOcupados;}
}

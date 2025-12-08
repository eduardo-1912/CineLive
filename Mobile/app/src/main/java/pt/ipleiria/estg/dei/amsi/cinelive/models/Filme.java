package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Filme {
    private int id;
    private String titulo, posterUrl, rating, generos, duracao, estreia, idioma, realizacao, sinopse;
    private boolean hasSessoes;

    public Filme(int id, String titulo, String posterUrl) {
        this.id = id;
        this.titulo = titulo;
        this.posterUrl = posterUrl;
    }

    public Filme(int id, String titulo, String posterUrl, String rating, String generos, String estreia, String duracao, String idioma, String realizacao, String sinopse, Boolean hasSessoes) {
        this.id = id;
        this.titulo = titulo;
        this.posterUrl = posterUrl;
        this.rating = rating;
        this.generos = generos;
        this.estreia = estreia;
        this.duracao = duracao;
        this.idioma = idioma;
        this.realizacao = realizacao;
        this.sinopse = sinopse;
        this.hasSessoes = hasSessoes;
    }

    public int getId() {return id;}
    public String getTitulo() {return titulo;}
    public String getRating() {return rating;}
    public String getGeneros() {return generos;}
    public String getSinopse() {return sinopse;}
    public String getDuracao() {return duracao;}
    public String getEstreia() {return estreia;}
    public String getIdioma() {return idioma;}
    public String getRealizacao() {return realizacao;}
    public String getPosterUrl() {return posterUrl;}
    public boolean hasSessoes() {return hasSessoes;}
}

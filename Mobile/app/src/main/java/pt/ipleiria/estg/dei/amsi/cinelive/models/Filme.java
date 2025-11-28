package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Filme {
    private int id;
    private String titulo, rating, generos, sinopse, duracao, estreia, idioma, realizacao, trailerUrl, posterUrl, estado;

    public Filme(int id, String titulo, String posterUrl) {
        this.id = id;
        this.titulo = titulo;
        this.posterUrl = posterUrl;
    }

    public Filme(int id, String titulo, String rating, String generos, String sinopse, String duracao, String estreia, String idioma, String realizacao, String posterUrl, String estado) {
        this.id = id;
        this.titulo = titulo;
        this.rating = rating;
        this.generos = generos;
        this.sinopse = sinopse;
        this.duracao = duracao;
        this.rating = rating;
        this.estreia = estreia;
        this.idioma = idioma;
        this.realizacao = realizacao;
        this.posterUrl = posterUrl;
        this.estado = estado;
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
    public String getEstado() {return estado;}


}

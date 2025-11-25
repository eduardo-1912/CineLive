package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Filme {
    public int id;
    public String titulo, sinopse, duracao, rating, estreia, idioma, realizacao, trailerUrl, posterUrl, estado;

    public Filme(int id, String titulo, String posterUrl) {
        this.id = id;
        this.titulo = titulo;
        this.posterUrl = posterUrl;
    }

    public Filme(int id, String titulo, String sinopse, String duracao, String rating, String estreia, String idioma, String realizacao, String posterUrl, String estado) {
        this.id = id;
        this.titulo = titulo;
        this.sinopse = sinopse;
        this.duracao = duracao;
        this.rating = rating;
        this.estreia = estreia;
        this.idioma = idioma;
        this.realizacao = realizacao;
        this.posterUrl = posterUrl;
        this.estado = estado;
    }
}

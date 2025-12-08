package pt.ipleiria.estg.dei.amsi.cinelive.utils;

public class ApiRoutes {
    public static String filmesEmExibicao (String url, int cinemaId) {
        return url + "/filmes?cinema_id=" + cinemaId;
    }

    public static String filmesKids (String url, int cinemaId) {
        return url + "/filmes?filter=kids&cinema_id=" + cinemaId;
    }

    public static String filmesBrevemente (String url) {
        return url + "/filmes?filter=brevemente";
    }

    public static String filme (String url, int filmeId) {
        return url + "/filmes/" + filmeId;
    }

    public static String sessoes (String url, int filmeId, int cinemaId) {
        return url + "/filmes/" + filmeId + "/sessoes?cinema_id=" + cinemaId ;
    }

    public static String sessao (String url, int sessaoId) {
        return url + "/sessoes/" + sessaoId;
    }

    public static String cinemas (String url) {
        return url + "/cinemas";
    }

    public static String compras (String url, String token) {
        return url + "/compras?access-token=" + token;
    }

    public static String compra (String url, int compraId, String token) {
        return url + "/compras/" + compraId + "?access-token=" + token;
    }

    public static String perfil (String url, String token) {
        return url + "/perfil" + "?access-token=" + token;
    }

    public static String login (String url) {
        return url + "/auth/login";
    }

    public static String signup (String url) {
        return url + "/auth/signup";
    }

    public static String validateToken (String url, String token) {
        return url + "/auth/validate-token?access-token=" + token;
    }
}

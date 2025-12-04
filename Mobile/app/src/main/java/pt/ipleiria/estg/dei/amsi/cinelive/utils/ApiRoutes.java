package pt.ipleiria.estg.dei.amsi.cinelive.utils;

public class ApiRoutes {
    public static String filmesEmExibicao (String url) {
        return url + "/filmes?cinema_id=";
    }

    public static String filmesKids (String url) {
        return url + "/filmes?filter=kids&cinema_id=";
    }

    public static String filmesBrevemente (String url) {
        return url + "/filmes?filter=brevemente";
    }

    public static String cinemas (String url) {
        return url + "/cinemas";
    }

    public static String compras (String url, String token) {
        return url + "/compras?access-token=" + token;
    }

    public static String compra (String url, int id, String token) {
        return url + "/compras/" + id + "?access-token=" + token;
    }

    public static String perfil ()
}

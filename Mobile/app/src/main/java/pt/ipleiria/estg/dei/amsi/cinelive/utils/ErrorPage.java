package pt.ipleiria.estg.dei.amsi.cinelive.utils;

import android.view.View;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.LayoutErrorBinding;

public class ErrorPage {
    public enum Type {INTERNET, API, CINEMA_INVALIDO, NENHUM_CINEMA, NENHUMA_COMPRA};

    public static void showError(LayoutErrorBinding binding, Type type) {
        switch(type) {
            case INTERNET:
                binding.ivIcon.setImageResource(R.drawable.ic_wifi_off);
                binding.tvTitulo.setText(R.string.erro_internet_titulo);
                binding.tvSubtitulo.setText(R.string.erro_internet_subtitulo);
                binding.btnAction.setText(R.string.btn_tentar_novamente);
                break;
            case API:
                binding.ivIcon.setImageResource(R.drawable.ic_api);
                binding.tvTitulo.setText(R.string.erro_api_titulo);
                binding.tvSubtitulo.setText(R.string.erro_api_subtitulo);
                binding.btnAction.setText(R.string.btn_configuracoes_api);
                break;
            case CINEMA_INVALIDO:
                binding.ivIcon.setImageResource(R.drawable.ic_cinemas);
                binding.tvTitulo.setText(R.string.erro_cinema_invalido_titulo);
                binding.tvSubtitulo.setText(R.string.erro_cinema_invalido_subtitulo);
                binding.btnAction.setText(R.string.btn_selecionar_cinema);
                break;
            case NENHUM_CINEMA:
                binding.ivIcon.setImageResource(R.drawable.ic_cinemas);
                binding.tvTitulo.setText(R.string.erro_nenhum_cinema_titulo);
                binding.tvSubtitulo.setText(R.string.erro_nenhum_cinema_subtitulo);
                binding.btnAction.setText(R.string.btn_tentar_novamente);
                break;
            case NENHUMA_COMPRA:
                binding.ivIcon.setImageResource(R.drawable.ic_compras);
                binding.tvTitulo.setText(R.string.erro_nenhuma_compra_titulo);
                binding.tvSubtitulo.setText(R.string.erro_nenhuma_compra_subtitulo);
                binding.btnAction.setText(R.string.btn_ver_filmes);
                break;
        }
    }
}

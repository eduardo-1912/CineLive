package pt.ipleiria.estg.dei.amsi.cinelive.utils;

import android.content.Context;
import android.view.View;
import android.widget.Toast;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.LayoutErrorBinding;

public class ErrorUtils {
    public enum Type {NO_INTERNET, API_ERROR, NENHUM_FILME, CINEMA_INVALIDO, EMPTY_CINEMAS, NENHUMA_COMPRA, INVALID_TOKEN}

    public static void showToast(Context context, Type type) {
        int stringRes;

        switch (type) {
            case NO_INTERNET:
                stringRes = R.string.title_erro_internet;
                break;
            case API_ERROR:
                stringRes = R.string.title_erro_api;
                break;
            default:
                stringRes = R.string.title_generic_error;
                break;
        }
        
        Toast.makeText(context, context.getString(stringRes), Toast.LENGTH_SHORT).show();
    }

    public static void showLayout(LayoutErrorBinding binding, Type type) {
        switch(type) {
            case NO_INTERNET:
                binding.ivIcon.setImageResource(R.drawable.ic_wifi_off);
                binding.tvTitulo.setText(R.string.title_erro_internet);
                binding.tvSubtitulo.setText(R.string.subtitle_erro_internet);
                binding.btnAction.setText(R.string.btn_tentar_novamente);
                break;
            case API_ERROR:
                binding.ivIcon.setImageResource(R.drawable.ic_api);
                binding.tvTitulo.setText(R.string.title_erro_api);
                binding.tvSubtitulo.setText(R.string.subtitle_erro_api);
                binding.btnAction.setText(R.string.btn_configuracoes);
                break;
            case NENHUM_FILME:
                binding.tvTitulo.setText(R.string.title_erro_nenhum_filme);
                binding.tvSubtitulo.setText(R.string.subtitle_erro_nenhum_filme);
                binding.btnAction.setVisibility(View.INVISIBLE);
                break;
            case CINEMA_INVALIDO:
                binding.ivIcon.setImageResource(R.drawable.ic_cinemas);
                binding.tvTitulo.setText(R.string.title_erro_cinema_invalido);
                binding.tvSubtitulo.setText(R.string.subtitle_erro_cinema_invalido);
                binding.btnAction.setText(R.string.btn_selecionar_cinema);
                break;
            case EMPTY_CINEMAS:
                binding.ivIcon.setImageResource(R.drawable.ic_cinemas);
                binding.tvTitulo.setText(R.string.title_erro_nenhum_cinema);
                binding.tvSubtitulo.setText(R.string.subtitle_erro_nenhum_cinema);
                binding.btnAction.setText(R.string.btn_tentar_novamente);
                break;
            case NENHUMA_COMPRA:
                binding.ivIcon.setImageResource(R.drawable.ic_compras);
                binding.tvTitulo.setText(R.string.title_erro_nenhuma_compra);
                binding.tvSubtitulo.setText(R.string.subtitle_erro_nenhuma_compra);
                binding.btnAction.setText(R.string.btn_ver_filmes);
                break;
            case INVALID_TOKEN:
                binding.ivIcon.setImageResource(R.drawable.ic_perfil);
                binding.tvTitulo.setText(R.string.title_erro_token_invalido);
                binding.tvSubtitulo.setText(R.string.subtitle_erro_token_invalido);
                binding.btnAction.setText(R.string.btn_logout);
                break;
        }
    }
}

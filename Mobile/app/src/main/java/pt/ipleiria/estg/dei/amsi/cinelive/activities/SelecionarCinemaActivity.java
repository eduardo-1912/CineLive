package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivitySelecionarCinemaBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CinemaListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ConnectionListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.CinemasManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class SelecionarCinemaActivity extends AppCompatActivity {

    ActivitySelecionarCinemaBinding binding;
    private PreferencesManager preferences;
    private CinemasManager cinemasManager;
    private String url;

    private enum Error {INTERNET, API}

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivitySelecionarCinemaBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // Refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);
            onResume();
        });

        // Aceder às preferences
        preferences = new PreferencesManager(this);

        // Se já tiver cinema --> continuar
        if (preferences.getCinemaId() != -1) startActivity(new Intent(this, MainActivity.class));

        cinemasManager = CinemasManager.getInstance();
    }

    @Override
    protected void onResume() {
        super.onResume();

        // Se não tem internet --> mostrar erro
        if (!ConnectionUtils.hasInternet(this)) {
            showErrorPage(Error.INTERNET);
            return;
        }

        // Obter URL da API
        url = preferences.getApiUrl();
        if (url == null) url = preferences.resetApiUrl();

        // Testar ligação à API
        ConnectionUtils.testApiConnection(this, url, new ConnectionListener() {
            @Override
            public void onSuccess(String response) {
                fetchCinemas();
            }
            @Override
            public void onError() {
                showErrorPage(Error.API);
            }
        });
    }

    private void fetchCinemas() {
        cinemasManager.getCinemasList(this, new CinemaListener() {
            @Override
            public void onCinemasLoaded(List<Cinema> cinemas) {
                binding.viewFlipper.setDisplayedChild(0);
                showCinemaList(cinemas);
            }

            @Override
            public void onError(String message) {
                showErrorPage(Error.API);
            }
        });
    }

    private void showCinemaList(List<Cinema> cinemas) {
        ArrayAdapter<Cinema> adapter = new ArrayAdapter<>(this, android.R.layout.simple_list_item_1, cinemas);
        binding.lvCinemas.setAdapter(adapter);

        binding.lvCinemas.setOnItemClickListener((parent, view, position, id) -> {
            // Obter o cinema e guardar nas preferences
            Cinema cinema = cinemas.get(position);
            preferences.setCinemaId(cinema.getId());

            // Continuar
            startActivity(new Intent(this, MainActivity.class));
            finish();
        });
    }

    private void showErrorPage(Error type) {

        binding.viewFlipper.setDisplayedChild(1);

        switch(type) {
            case INTERNET:
                binding.error.ivIcon.setImageResource(R.drawable.ic_wifi_off);
                binding.error.tvTitulo.setText(R.string.erro_internet_titulo);
                binding.error.btnReload.setVisibility(View.VISIBLE);
                binding.error.btnConfiguracoes.setVisibility(View.GONE);
                binding.error.btnReload.setOnClickListener(v -> onResume());
                break;
            case API:
                binding.error.ivIcon.setImageResource(R.drawable.ic_api);
                binding.error.tvTitulo.setText(R.string.erro_api_titulo);
                binding.error.btnConfiguracoes.setVisibility(View.VISIBLE);
                binding.error.btnReload.setVisibility(View.GONE);
                binding.error.btnConfiguracoes.setOnClickListener(v -> {
                    startActivity(new Intent(this, ConfiguracoesActivity.class));
                });
                break;
        }
    }
}
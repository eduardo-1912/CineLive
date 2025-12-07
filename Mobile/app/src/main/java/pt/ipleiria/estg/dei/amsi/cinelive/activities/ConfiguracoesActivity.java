package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import org.json.JSONObject;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityConfiguracoesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ConnectionListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class ConfiguracoesActivity extends AppCompatActivity {

    ActivityConfiguracoesBinding binding;
    PreferencesManager preferences;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityConfiguracoesBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle(R.string.configuracoes);
        binding.toolbar.statusIcon.setVisibility(View.VISIBLE);

        // Aceder às preferences
        preferences = new PreferencesManager(this);

        // Colocar dados nos campos
        binding.etApiHost.setText(preferences.getApiHost());
        binding.etApiPath.setText(preferences.getApiPath());

        // Testar ligação à API
        testApiConnection(preferences.getApiUrl(), ConnectionUtils.FAST_TIMEOUT, false);

        // Botão Guardar
        binding.btnGuardar.setOnClickListener(v -> {
            String apiHost = String.valueOf(binding.etApiHost.getText());
            String apiPath = String.valueOf(binding.etApiPath.getText());

            // Testar ligação à API
            testApiConnection(apiHost + apiPath, ConnectionUtils.DEFAULT_TIMEOUT, true);

            // Guardar nas preferences
            preferences.setApiUrl(apiHost, apiPath);
        });

        // Botão Restaurar
        binding.btnRestaurar.setOnClickListener(v -> {
            // Reset para default
            preferences.resetApiUrl();

            binding.etApiHost.setText(preferences.getApiHost());
            binding.etApiPath.setText(preferences.getApiPath());

            // Testar ligação à API
            testApiConnection(preferences.getApiUrl(), ConnectionUtils.DEFAULT_TIMEOUT, true);
        });
    }

    private void testApiConnection(String url, int timeout, boolean blockUI) {

        // Verificar ligação à internet
        if (!ConnectionUtils.hasInternet(this)) {
            Toast.makeText(this, R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
            return;
        }

        showProgressBar(blockUI);
        updateStatusIcon(0);

        ConnectionUtils.testApiConnection(this, url, timeout, new ConnectionListener() {
            @Override
            public void onSuccess(String response) {
                showProgressBar(false);
                updateStatusIcon(1);

                try {
                    JSONObject json = new JSONObject(response);
                    binding.tvApiResponse.setVisibility(View.VISIBLE);
                    binding.tvApiResponse.setText(json.toString(3));
                }
                catch (Exception e) {
                    binding.tvApiResponse.setVisibility(View.GONE);
                }
            }
            @Override
            public void onError() {
                binding.tvApiResponse.setVisibility(View.GONE);
                showProgressBar(false);
                updateStatusIcon(2);
                Toast.makeText(ConfiguracoesActivity.this, R.string.erro_api_titulo, Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void updateStatusIcon(int status) {
        if (status == 0) binding.toolbar.statusIcon.setImageResource(R.drawable.circle_gray);
        else if (status == 1) binding.toolbar.statusIcon.setImageResource(R.drawable.circle_green);
        else binding.toolbar.statusIcon.setImageResource(R.drawable.circle_red);
    }

    private void showProgressBar(boolean show) {
        binding.tvApiResponse.setVisibility(View.GONE);
        binding.progressBar.setVisibility(show ? View.VISIBLE : View.GONE);
        binding.btnGuardar.setEnabled(!show);
        binding.btnRestaurar.setEnabled(!show);
        binding.etApiHost.setEnabled(!show);
        binding.etApiPath.setEnabled(!show);
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}
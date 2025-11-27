package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityDetalhesFilmeBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.NetworkUtils;

public class DetalhesFilmeActivity extends AppCompatActivity {

    ActivityDetalhesFilmeBinding binding;
    Sessao sessaoSelecionada;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityDetalhesFilmeBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        // Obter ID do Filme
        Intent intentFilmes = getIntent();
        int idFilme = intentFilmes.getIntExtra("filme_id", -1);

        getSupportActionBar().setTitle("TÍTULO DO FILME......"); // TODO: REPLACE THIS

        // TODO: REPLACE MOCK-DATA
        Filme filme = new Filme(1, "Carros 2", "M3", "Ação, Aventura",
                "Um grande campeão das pistas é lançado numa corrida internacional enquanto o seu amigo Mate é apanhado num enredo de espionagem que põe à prova a amizade de ambos e mostra que coragem pode surgir dos lugares mais improváveis.",
                "2h 32min", "25/11/2025", "Português", "John Lasseter", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_6910b6ad1f9ea.jpg", "Em exibição");

        // Carregar Poster
        Glide.with(this)
                .load(filme.posterUrl)
                .placeholder(R.drawable.poster_placeholder)
                .diskCacheStrategy(DiskCacheStrategy.ALL)
                .into(binding.ivPoster);

        // Dados do filmes
        binding.tvTitulo.setText(filme.getTitulo());
        binding.tvRating.setText(filme.getRating());
        binding.tvGeneros.setText(filme.getGeneros());
        binding.tvEstreia.setText(filme.getEstreia());
        binding.tvDuracao.setText(filme.getDuracao());
        binding.tvIdioma.setText(filme.getIdioma());
        binding.tvRealizacao.setText(filme.getRealizacao());
        binding.tvSinopse.setText(filme.getSinopse());

        // Array associativo de sessões por data
        Map<String, List<Sessao>> sessoesPorData = new LinkedHashMap<>();

        // TODO: REPLACE MOCK DATA
        sessoesPorData.put("28/11/2025", Arrays.asList(
                new Sessao(7, "28/11/2025", "16:00"),
                new Sessao(8, "28/11/2025", "21:30")
        ));
        sessoesPorData.put("29/11/2025", Arrays.asList(
                new Sessao(9, "29/11/2025", "14:00"),
                new Sessao(10, "29/11/2025", "18:00"),
                new Sessao(11, "29/11/2025", "22:00")
        ));

        // Array de datas
        List<String> datas = new ArrayList<>(sessoesPorData.keySet());

        binding.spinnerData.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, datas));
        binding.spinnerData.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {

                // Obter data selecionada
                String data = datas.get(position);

                // Obter sessões da data selecionada
                List<Sessao> sessoes = sessoesPorData.get(data);

                // Adicionar todas as sessões à lista
                List<String> horas = new ArrayList<>();
                for (Sessao sessao : sessoes) horas.add(sessao.getHoraInicio());

                binding.lvHoras.setAdapter(new ArrayAdapter<>(DetalhesFilmeActivity.this, android.R.layout.simple_list_item_1, horas));

                // Selecionou uma sessão
                binding.lvHoras.setOnItemClickListener((p, v, pos, i) -> {
                    Sessao sessao = sessoes.get(pos);
                    Intent intentSessao = new Intent(DetalhesFilmeActivity.this, ComprarBilhetesActivity.class);

                    // Passar dados do filme e a sessão
                    intentSessao.putExtra("sessao_id", sessao.getId());
                    intentSessao.putExtra("titulo", filme.getTitulo());
                    intentSessao.putExtra("rating", filme.getRating());
                    intentSessao.putExtra("duracao", filme.getDuracao());

                    startActivity(intentSessao);
                });
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });
    }

    @Override
    public void onResume()
    {
        super.onResume();
        if (!NetworkUtils.hasInternet(this)) {
            Toast.makeText(this, R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}